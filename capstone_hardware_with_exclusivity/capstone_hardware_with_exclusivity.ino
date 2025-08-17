#include <ESP32Servo.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <HX711.h>

// WiFi credentials
const char* ssid = "iPhone";
const char* password = "gelo12345";
const char* serverName = "http://172.20.10.2/CAPSTONEE/endpoint.php";

// HX711 weight sensor pins
#define DOUT 16 //(blue)
#define SCK 17 //(violet)
HX711 scale;

// #define DOUT 26
// #define SCK 27
// HX711 scale;

// #define DOUT 26
// #define SCK 27
// HX711 scale;


// Servo setup
Servo servoPlastic;
Servo servoGlass;
Servo servoCan;

const int SERVO_PIN1 = 25; // Plastic
const int SERVO_PIN2 = 26; // Glass
const int SERVO_PIN3 = 27; // Can

// Buzzer pin
const int BUZZER_PIN = 23; // Adjust according to your wiring


// User info
int userID = 0;
bool userIDSet = false;
String username = "";

// Sensor pins
const int SENSOR_PIN_1 = 34; // plastic bottle
const int SENSOR_PIN_2 = 35; // glass bottle
const int SENSOR_PIN_3 = 32; // tin cans

// Reading validation
const int NUM_READINGS = 10;
const int READING_DELAY = 50;

// Thresholds
const int PLASTIC_THRESHOLD_MIN = 48;
const int PLASTIC_THRESHOLD_MAX = 100;
const int BOTTLE_THRESHOLD_MIN = 20;
const int BOTTLE_THRESHOLD_MAX = 47;
const int CAN_THRESHOLD_MIN = 101;
const int CAN_THRESHOLD_MAX = 300;
const int CONFIDENCE_THRESHOLD = 6;

// Timing
unsigned long previousMillis = 0;
const long interval = 5000; // 5 seconds

bool fetchCurrentUser() {
    HTTPClient http;
    http.begin("http://172.20.10.2/CAPSTONEE/get_current_user.php");
    int httpResponseCode = http.GET();
    if (httpResponseCode > 0) {
        String response = http.getString();
        Serial.println("Response: " + response);
        DynamicJsonDocument doc(1024);
        DeserializationError error = deserializeJson(doc, response);
        if (!error && doc.containsKey("userID")) {
            userID = doc["userID"];
            username = doc["username"] | "Unknown";
            userIDSet = true;
            Serial.print("Current user: "); Serial.println(username);
            Serial.print("UserID: "); Serial.println(userID);
            return true;
        } else {
            Serial.println("No user currently logged in or JSON parsing error.");
            Serial.println("Error: " + String(error.c_str()));
        }
    } else {
        Serial.print("Error fetching user: "); Serial.println(httpResponseCode);
    }
    http.end();
    return false;
}

int getAverageReading(int sensorPin) {
    int total = 0;
    int validReadings = 0;
    for (int i = 0; i < NUM_READINGS; i++) {
        int reading = analogRead(sensorPin);
        if (reading > 0) {
            total += reading;
            validReadings++;
        }
        delay(READING_DELAY);
    }
    if (validReadings == 0) return 0;
    return total / validReadings;
}

String determineMaterial() {
    int plasticCount = 0;
    int bottleCount = 0;
    int canCount = 0;

    Serial.println("--- Material Determination ---");
    for (int i = 0; i < NUM_READINGS; i++) {
        int plasticReading = analogRead(SENSOR_PIN_1);
        int bottleReading = analogRead(SENSOR_PIN_2);
        int canReading = analogRead(SENSOR_PIN_3);

        Serial.print("Reading "); Serial.print(i + 1); Serial.print(": ");
        Serial.print("Plastic="); Serial.print(plasticReading); Serial.print(" | ");
        Serial.print("Glass="); Serial.print(bottleReading); Serial.print(" | ");
        Serial.print("Can="); Serial.println(canReading);

        // Plastic condition: only plastic sensor in threshold
        if (plasticReading >= PLASTIC_THRESHOLD_MIN && plasticReading <= PLASTIC_THRESHOLD_MAX &&
            !(bottleReading >= BOTTLE_THRESHOLD_MIN && bottleReading <= BOTTLE_THRESHOLD_MAX) &&
            !(canReading >= CAN_THRESHOLD_MIN && canReading <= CAN_THRESHOLD_MAX)) {
            plasticCount++;
        }

        // Glass condition: only glass sensor in threshold
        else if (bottleReading >= BOTTLE_THRESHOLD_MIN && bottleReading <= BOTTLE_THRESHOLD_MAX &&
                 !(plasticReading >= PLASTIC_THRESHOLD_MIN && plasticReading <= PLASTIC_THRESHOLD_MAX) &&
                 !(canReading >= CAN_THRESHOLD_MIN && canReading <= CAN_THRESHOLD_MAX)) {
            bottleCount++;
        }

        // Can condition: only can sensor in threshold
        else if (canReading >= CAN_THRESHOLD_MIN && canReading <= CAN_THRESHOLD_MAX &&
                 !(plasticReading >= PLASTIC_THRESHOLD_MIN && plasticReading <= PLASTIC_THRESHOLD_MAX) &&
                 !(bottleReading >= BOTTLE_THRESHOLD_MIN && bottleReading <= BOTTLE_THRESHOLD_MAX)) {
            canCount++;
        }

        delay(READING_DELAY);
    }

    Serial.print("Plastic count: "); Serial.println(plasticCount);
    Serial.print("Glass count: "); Serial.println(bottleCount);
    Serial.print("Can count: "); Serial.println(canCount);

    if (plasticCount >= CONFIDENCE_THRESHOLD) return "Plastic Bottles";
    else if (bottleCount >= CONFIDENCE_THRESHOLD) return "Glass Bottles";
    else if (canCount >= CONFIDENCE_THRESHOLD) return "Cans";

    return "unknown";
}


void setup() {
    Serial.begin(9600);
    delay(2000);

    // Initialize WiFi
    WiFi.begin(ssid, password);
    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }
    Serial.println("WiFi Connected");

    fetchCurrentUser();

    // Initialize HX711
    scale.begin(DOUT, SCK);
    scale.set_scale(); // Calibrate and set your scale factor here
    scale.tare(); // Reset scale to zero

    // Initialize servo
    servoPlastic.attach(SERVO_PIN1);
    servoGlass.attach(SERVO_PIN2);
    servoCan.attach(SERVO_PIN3);

    servoPlastic.write(0);
    servoGlass.write(0);
    servoCan.write(0);


    // Initialize buzzer
    pinMode(BUZZER_PIN, OUTPUT);
    digitalWrite(BUZZER_PIN, LOW);

    Serial.println("Setup complete");
}

void loop() {
    unsigned long currentMillis = millis();

    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("WiFi disconnected, attempting reconnect...");
        WiFi.begin(ssid, password);
        delay(5000);
        if (WiFi.status() == WL_CONNECTED) {
            Serial.println("WiFi reconnected!");
            fetchCurrentUser();
        }
        return;
    }

    if (currentMillis - previousMillis >= interval) {
        previousMillis = currentMillis;

        String materialType = determineMaterial();
        Serial.print("Detected Material: "); Serial.println(materialType);

        if (materialType != "unknown") {
            // Activate buzzer
            digitalWrite(BUZZER_PIN, HIGH);
            delay(500);
            digitalWrite(BUZZER_PIN, LOW);

            // Read weight
            float weight = scale.get_units(5); // average of 5 readings
            Serial.print("Weight: "); Serial.print(weight); Serial.println(" grams");

           if (materialType == "Plastic Bottles") {
    servoPlastic.write(90);
    delay(2000);
    servoPlastic.write(0);
        } else if (materialType == "Glass Bottles") {
    servoGlass.write(90);
    delay(2000);
    servoGlass.write(0);
    } else if (materialType == "Cans") {
    servoCan.write(90);
    delay(2000);
    servoCan.write(0);
    }


            // Prepare HTTP POST
            if (userIDSet) {
                HTTPClient http;
                http.begin(serverName);
                http.addHeader("Content-Type", "application/x-www-form-urlencoded");

                String httpRequestData = "material=" + materialType +
                                         "&weight=" + String(weight, 2) +
                                         "&userID=" + String(userID);

                Serial.print("Sending data: "); Serial.println(httpRequestData);

                int httpResponseCode = http.POST(httpRequestData);
                Serial.print("HTTP Response code: "); Serial.println(httpResponseCode);
                if (httpResponseCode > 0) {
                    String response = http.getString();
                    Serial.println("Response: " + response);
                } else {
                    Serial.print("POST error: "); Serial.println(httpResponseCode);
                }
                http.end();
            } else {
                Serial.println("User ID not set, skipping POST");
            }
        } else {
            Serial.println("Material not detected. Servo will not move. Buzzer will remain silent.");
        }
    }
}
