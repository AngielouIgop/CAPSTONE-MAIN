#include <ESP32Servo.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <HX711.h>

// WiFi credentials
const char* ssid = "iPhone";
const char* password = "gelo12345";
const char* serverName = "http://172.20.10.2/CAPSTONE-MAIN/endpoints/endpoint.php";
const char* statusCheckUrl = "http://172.20.10.2/CAPSTONE-MAIN/endpoints/checkContributionStatus.php";

// HX711 weight sensors (Plastic, Glass, Can)
#define DOUT_PLASTIC 33
#define SCK_PLASTIC 25
HX711 scalePlastic;

#define DOUT_GLASS 26
#define SCK_GLASS 27
HX711 scaleGlass;

#define DOUT_CAN 14
#define SCK_CAN 12
HX711 scaleCan;

// Servo setup
Servo servoPlastic;
Servo servoGlass;
Servo servoCan;

const int SERVO_PIN1 = 23; // Plastic
const int SERVO_PIN2 = 22; // Glass
const int SERVO_PIN3 = 21; // Can

// Buzzer pin
const int BUZZER_PIN = 5;

// Ultrasonic sensor

//Plastic Bin
#define TRIG_PIN 18
#define ECHO_PIN 19
// Glass Bin
#define TRIG_PIN 16
#define ECHO_PIN 17
//Can Bin
#define TRIG_PIN 15
#define ECHO_PIN 2
const int BIN_FULL_DISTANCE = 10;



// Global flag for bin full notification
bool plasticBinFullNotified = false;
bool glassBinFullNotified = false;
bool canBinFullNotified = false;

// User info
int userID = 0;
bool userIDSet = false;
String username = "";
bool contributionStarted = false;

// Sensor pins
const int SENSOR_PIN_1 = 34; // plastic bottle
const int SENSOR_PIN_2 = 35; // glass bottle
const int SENSOR_PIN_3 = 32; // tin cans

// Reading validation
const int NUM_READINGS = 10;
const int READING_DELAY = 50;

// Thresholds
const int PLASTIC_THRESHOLD_MIN = 47;
const int PLASTIC_THRESHOLD_MAX = 80;
const int BOTTLE_THRESHOLD_MIN = 1;
const int BOTTLE_THRESHOLD_MAX = 21;
const int CAN_THRESHOLD_MIN = 22;
const int CAN_THRESHOLD_MAX = 46;
const int PLASTIC_CONFIDENCE_THRESHOLD = 6;
const int BOTTLE_CONFIDENCE_THRESHOLD = 5;
const int CAN_CONFIDENCE_THRESHOLD = 5;

// Timing
unsigned long previousMillis = 0;
const long interval = 5000; // 5 seconds
unsigned long lastStatusCheck = 0;
const long statusCheckInterval = 2000; // Check status every 2 seconds

long getDistance() {
  digitalWrite(TRIG_PIN, LOW);
  delayMicroseconds(2);
  digitalWrite(TRIG_PIN, HIGH);
  delayMicroseconds(10);
  digitalWrite(TRIG_PIN, LOW);

  long duration = pulseIn(ECHO_PIN, HIGH);
  long distance = duration * 0.034 / 2; // cm
  return distance;
}


bool fetchCurrentUser() {
    HTTPClient http;
    http.begin("http://172.20.10.2/CAPSTONE-MAIN/endpoints/get_current_user.php");
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

bool checkContributionStatus() {
    HTTPClient http;
    http.begin(statusCheckUrl);
    int httpResponseCode = http.GET();
    if (httpResponseCode > 0) {
        String response = http.getString();
        Serial.println("Status check response: " + response);
        DynamicJsonDocument doc(1024);
        DeserializationError error = deserializeJson(doc, response);
        if (!error && doc.containsKey("contribution_started")) {
            bool newStatus = doc["contribution_started"];
            if (newStatus != contributionStarted) {
                contributionStarted = newStatus;
                if (contributionStarted) {
                    Serial.println("Contribution STARTED - Ready to accept waste materials!");
                    // Activate buzzer to indicate ready
                    digitalWrite(BUZZER_PIN, HIGH);
                    delay(200);
                    digitalWrite(BUZZER_PIN, LOW);
                    delay(200);
                    digitalWrite(BUZZER_PIN, HIGH);
                    delay(200);
                    digitalWrite(BUZZER_PIN, LOW);
                } else {
                    Serial.println("Contribution STOPPED - Waiting for user to start...");
                }
            }
            return true;
        }
    } else {
        Serial.print("Error checking status: "); Serial.println(httpResponseCode);
    }
    http.end();
    return false;
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

        if (plasticReading >= PLASTIC_THRESHOLD_MIN && plasticReading <= PLASTIC_THRESHOLD_MAX &&
            !(bottleReading >= PLASTIC_THRESHOLD_MIN && bottleReading <= PLASTIC_THRESHOLD_MAX) &&
            !(canReading >= PLASTIC_THRESHOLD_MIN && canReading <= PLASTIC_THRESHOLD_MAX)) {
            plasticCount++;
        }
        else if (bottleReading >= BOTTLE_THRESHOLD_MIN && bottleReading <= BOTTLE_THRESHOLD_MAX &&
                 !(plasticReading >= BOTTLE_THRESHOLD_MIN && plasticReading <= BOTTLE_THRESHOLD_MAX) &&
                 !(canReading >= BOTTLE_THRESHOLD_MIN && canReading <= BOTTLE_THRESHOLD_MAX)) {
            bottleCount++;
        }
        else if (canReading >= CAN_THRESHOLD_MIN && canReading <= CAN_THRESHOLD_MAX &&
                 !(plasticReading >= CAN_THRESHOLD_MIN && plasticReading <= CAN_THRESHOLD_MAX) &&
                 !(bottleReading >= CAN_THRESHOLD_MIN && bottleReading <= CAN_THRESHOLD_MAX)) {
            canCount++;
        }
        delay(READING_DELAY);        
    }    

    Serial.print("Plastic count: "); Serial.println(plasticCount);
    Serial.print("Glass count: "); Serial.println(bottleCount);
    Serial.print("Can count: "); Serial.println(canCount);

    if (plasticCount >= PLASTIC_CONFIDENCE_THRESHOLD) return "Plastic Bottles";
    else if (bottleCount >= BOTTLE_CONFIDENCE_THRESHOLD) return "Glass Bottles";
    else if (canCount >= CAN_CONFIDENCE_THRESHOLD) return "Cans";

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

    // Initialize HX711 sensors
    scalePlastic.begin(DOUT_PLASTIC, SCK_PLASTIC);
    scalePlastic.set_scale();
    scalePlastic.tare();

    scaleGlass.begin(DOUT_GLASS, SCK_GLASS);
    scaleGlass.set_scale();
    scaleGlass.tare();

    scaleCan.begin(DOUT_CAN, SCK_CAN);
    scaleCan.set_scale();
    scaleCan.tare();

    // Initialize servos
    servoPlastic.attach(SERVO_PIN1);
    servoGlass.attach(SERVO_PIN2);
    servoCan.attach(SERVO_PIN3);

    servoPlastic.write(0);
    servoGlass.write(0);
    servoCan.write(0);

    pinMode(BUZZER_PIN, OUTPUT);
    digitalWrite(BUZZER_PIN, LOW);

    pinMode(TRIG_PIN, OUTPUT);
    pinMode(ECHO_PIN, INPUT);

    Serial.println("Setup complete");
    Serial.println("Waiting for user to start contributing...");
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

    // Check contribution status every 2 seconds
    if (currentMillis - lastStatusCheck >= statusCheckInterval) {
        lastStatusCheck = currentMillis;
        checkContributionStatus();
    }

    // Only process waste materials if contribution has started
    if (contributionStarted && currentMillis - previousMillis >= interval) {
        previousMillis = currentMillis;

        String materialType = determineMaterial();
        Serial.print("Detected Material: "); Serial.println(materialType);

        if (materialType != "unknown") {
            digitalWrite(BUZZER_PIN, HIGH);
            delay(500);
            digitalWrite(BUZZER_PIN, LOW);

            float weight = 0;
            if (materialType == "Plastic Bottles") {
            weight = scalePlastic.get_units(5);
            servoPlastic.write(90);
            delay(2000);  // ✅ Slowest
            servoPlastic.write(0);
            } else if (materialType == "Glass Bottles") {
            weight = scaleGlass.get_units(5);
            servoGlass.write(90);
            delay(1500);  // ✅ Faster than plastic
            servoGlass.write(0);
            }else if (materialType == "Cans") {
            weight = scaleCan.get_units(5);
            servoCan.write(90);
            delay(1000);  // ✅ Fastest
            servoCan.write(0);
            }

            Serial.print("Weight: "); Serial.print(weight); Serial.println(" grams");

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
    } else if (!contributionStarted) {
        // Optional: Add a small delay when waiting to reduce CPU usage
        delay(100);
    }

    // ✅ Bin full detection for Plastic Bin
    long distance = getDistance();
    Serial.print("Plastic bin distance: ");
    Serial.println(distance);

    if (distance <= BIN_FULL_DISTANCE && !plasticBinFullNotified) {
        Serial.println("Plastic bin is nearly full!");

        if (WiFi.status() == WL_CONNECTED) {
            HTTPClient http;
            http.begin("http://172.20.10.2/CAPSTONE-MAIN/endpoints/notifEndpoint.php");   
            http.addHeader("Content-Type", "application/x-www-form-urlencoded");

            String postData = "sensor_name=Plastic Bin&message=Plastic bin is nearly full!&status=unread";
            int httpResponseCode = http.POST(postData);

            Serial.print("Plastic bin full notify response code: ");
            Serial.println(httpResponseCode);

            if (httpResponseCode > 0) {
                String response = http.getString();
                Serial.println(response);
                if (httpResponseCode == 200) {
                    plasticBinFullNotified = true; // ✅ avoid spamming
                }
            } else {
                Serial.print("Error in sending plastic bin full request: ");
                Serial.println(httpResponseCode);
            }
            http.end();
        }
    }
    else if (distance > BIN_FULL_DISTANCE) {
        plasticBinFullNotified = false; // ✅ reset once bin is emptied
    }

    // ✅ Bin full detection for Glass Bin
    Serial.print("Glass bin distance: ");
    Serial.println(distance);

    if (distance <= BIN_FULL_DISTANCE && !glassBinFullNotified) {
        Serial.println("Glass bin is nearly full!");

        if (WiFi.status() == WL_CONNECTED) {
            HTTPClient http;
            http.begin("http://172.20.10.2/CAPSTONE-MAIN/endpoints/notifEndpoint.php");   
            http.addHeader("Content-Type", "application/x-www-form-urlencoded");

            String postData = "sensor_name=Glass Bin&message=Glass bin is nearly full!&status=unread";
            int httpResponseCode = http.POST(postData);

            Serial.print("Glass bin full notify response code: ");
            Serial.println(httpResponseCode);

            if (httpResponseCode > 0) {
                String response = http.getString();
                Serial.println(response);
                if (httpResponseCode == 200) {
                    glassBinFullNotified = true; // ✅ avoid spamming
                }
            } else {
                Serial.print("Error in sending glass bin full request: ");
                Serial.println(httpResponseCode);
            }
            http.end();
        }
    }
    else if (distance > BIN_FULL_DISTANCE) {
        glassBinFullNotified = false; // ✅ reset once bin is emptied
    }

    // ✅ Bin full detection for Can Bin
    Serial.print("Can bin distance: ");
    Serial.println(distance);

    if (distance <= BIN_FULL_DISTANCE && !canBinFullNotified) {
        Serial.println("Can bin is nearly full!");

        if (WiFi.status() == WL_CONNECTED) {
            HTTPClient http;
            http.begin("http://172.20.10.2/CAPSTONE-MAIN/endpoints/notifEndpoint.php");   
            http.addHeader("Content-Type", "application/x-www-form-urlencoded");

            String postData = "sensor_name=Can Bin&message=Can bin is nearly full!&status=unread";
            int httpResponseCode = http.POST(postData);

            Serial.print("Can bin full notify response code: ");
            Serial.println(httpResponseCode);

            if (httpResponseCode > 0) {
                String response = http.getString();
                Serial.println(response);
                if (httpResponseCode == 200) {
                    canBinFullNotified = true; // ✅ avoid spamming
                }
            } else {
                Serial.print("Error in sending can bin full request: ");
                Serial.println(httpResponseCode);
            }
            http.end();
        }
    }
    else if (distance > BIN_FULL_DISTANCE) {
        canBinFullNotified = false; // ✅ reset once bin is emptied
    }
}