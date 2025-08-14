#include <ESP32Servo.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <HX711.h>

// WiFi credentials
const char* ssid = "PLDTHOMEFIBR726d0";
const char* password = "PLDTWIFI2f7xd";
const char* serverName = "http://192.168.1.13/CAPSTONEE/endpoint.php";

// HX711 weight sensor pins
#define DOUT 26
#define SCK 27
HX711 scale;

// Servo setup
Servo trapdoorServo;
const int SERVO_PIN = 25;

// Buzzer pin
const int BUZZER_PIN = 33; // Adjust according to your wiring

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
const int PLASTIC_THRESHOLD_MIN = 44;
const int PLASTIC_THRESHOLD_MAX = 100;
const int BOTTLE_THRESHOLD_MIN = 10;
const int BOTTLE_THRESHOLD_MAX = 41;
const int CAN_THRESHOLD_MIN = 101;
const int CAN_THRESHOLD_MAX = 300;
const int CONFIDENCE_THRESHOLD = 6;

// Timing
unsigned long previousMillis = 0;
const long interval = 5000; // 5 seconds

bool fetchCurrentUser() {
    HTTPClient http;
    http.begin("http://192.168.1.13/CAPSTONEE/get_current_user.php");
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

        if (plasticReading >= PLASTIC_THRESHOLD_MIN && plasticReading <= PLASTIC_THRESHOLD_MAX)
            plasticCount++;
        if (bottleReading >= BOTTLE_THRESHOLD_MIN && bottleReading <= BOTTLE_THRESHOLD_MAX)
            bottleCount++;
        if (canReading >= CAN_THRESHOLD_MIN && canReading <= CAN_THRESHOLD_MAX)
            canCount++;

        delay(READING_DELAY);
    }

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
    trapdoorServo.attach(SERVO_PIN);
    trapdoorServo.write(0); // Closed position

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

            // Open servo (trapdoor)
            trapdoorServo.write(90); // open
            delay(2000);
            trapdoorServo.write(0); // close

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
