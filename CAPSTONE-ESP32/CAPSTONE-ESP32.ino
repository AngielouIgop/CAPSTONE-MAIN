/*
 * ESP32 Waste Management System
 * Handles material detection, weight measurement, and servo control
 */

#include <ESP32Servo.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <HX711.h>

// ==================== NETWORK CONFIGURATION ====================
const char* ssid = "iPhone";
const char* password = "gelo1234";
const char* serverName = "http://172.20.10.4/CAPSTONE-MAIN/endpoints/endpoint.php";
const char* statusCheckUrl = "http://172.20.10.4/CAPSTONE-MAIN/endpoints/checkContributionStatus.php";
const char* claimServoUrl = "http://172.20.10.4/CAPSTONE-MAIN/endpoints/checkClaimServo.php";

// ==================== HX711 WEIGHT SENSORS ====================
#define DOUT_PLASTIC 33
#define SCK_PLASTIC 25
HX711 scalePlastic;

#define DOUT_GLASS 26
#define SCK_GLASS 27
HX711 scaleGlass;

#define DOUT_CAN 14
#define SCK_CAN 12
HX711 scaleCan;

// ==================== SERVO MOTORS ====================
Servo servoPlastic;
Servo servoGlass;
Servo servoCan;

const int SERVO_PIN1 = 23; // Plastic
const int SERVO_PIN2 = 22; // Glass
const int SERVO_PIN3 = 21; // Can

// ==================== HARDWARE PINS ====================
const int BUZZER_PIN = 18;

// ==================== ARDUINO COMMUNICATION ====================
const int ARDUINO_RX = 16;
const int ARDUINO_TX = 17;

// ==================== MATERIAL DETECTION SENSORS ====================
const int SENSOR_PIN_1 = 34; // Plastic bottle
const int SENSOR_PIN_2 = 35; // Glass bottle
const int SENSOR_PIN_3 = 32; // Tin cans

// ==================== DETECTION THRESHOLDS ====================
const int PLASTIC_THRESHOLD_MIN = 48;
const int PLASTIC_THRESHOLD_MAX = 85;
const int BOTTLE_THRESHOLD_MIN = 1;
const int BOTTLE_THRESHOLD_MAX = 29;
const int CAN_THRESHOLD_MIN = 30;
const int CAN_THRESHOLD_MAX = 47;

const int PLASTIC_CONFIDENCE_THRESHOLD = 6;
const int BOTTLE_CONFIDENCE_THRESHOLD = 5;
const int CAN_CONFIDENCE_THRESHOLD = 5;

// ==================== READING VALIDATION ====================
const int NUM_READINGS = 10;
const int READING_DELAY = 50;

// ==================== TIMING CONFIGURATION ====================
unsigned long previousMillis = 0;
const long interval = 5000;
unsigned long lastStatusCheck = 0;
const long statusCheckInterval = 500;
unsigned long lastClaimCheck = 0;
const long claimCheckInterval = 1000;
unsigned long lastArduinoCheck = 0;
const long arduinoCheckInterval = 1000;

// ==================== SYSTEM STATE VARIABLES ====================
bool plasticBinFullNotified = false;
bool glassBinFullNotified = false;
bool canBinFullNotified = false;

int userID = 0;
bool userIDSet = false;
String username = "";
bool contributionStarted = false;

// ==================== ARDUINO COMMUNICATION FUNCTIONS ====================
void sendToArduino(String command) {
    Serial2.println(command);
    Serial.println("Sent to Arduino: " + command);
}

void checkArduinoCommunication() {
    if (Serial2.available()) {
        String arduinoMessage = Serial2.readStringUntil('\n');
        arduinoMessage.trim();
        Serial.println("Received from Arduino: " + arduinoMessage);
        
        if (arduinoMessage.startsWith("ARDUINO_READY")) {
            Serial.println("Arduino Uno is ready");
        }
        else if (arduinoMessage.startsWith("CLAIM_SERVO_COMPLETE:")) {
            String slotNum = arduinoMessage.substring(20);
            Serial.println("Claim servo completed for slot " + slotNum);
        }
        else if (arduinoMessage.startsWith("BIN_FULL:")) {
            String binType = arduinoMessage.substring(9);
            binType.trim();
            Serial.println("BIN_FULL received: " + binType);
            handleBinFullNotification(binType);
        }
        else if (arduinoMessage.startsWith("BIN_EMPTY:")) {
            String binType = arduinoMessage.substring(10);
            binType.trim();
            Serial.println("BIN_EMPTY received: " + binType);
            handleBinEmptyNotification(binType);
        }
        else if (arduinoMessage == "PONG") {
            Serial.println("PONG received from Arduino");
        }
        else if (arduinoMessage == "TEST_RESPONSE") {
            Serial.println("TEST_RESPONSE received from Arduino");
        }
    }
}

// ==================== BIN MANAGEMENT FUNCTIONS ====================
void handleBinFullNotification(String binType) {
    if (binType == "PLASTIC" && !plasticBinFullNotified) {
        plasticBinFullNotified = true;
        sendBinFullNotification("Plastic Bin", "Plastic bin is nearly full!");
    } else if (binType == "GLASS" && !glassBinFullNotified) {
        glassBinFullNotified = true;
        sendBinFullNotification("Glass Bin", "Glass bin is nearly full!");
    } else if (binType == "CAN" && !canBinFullNotified) {
        canBinFullNotified = true;
        sendBinFullNotification("Can Bin", "Can bin is nearly full!");
    }
}

void handleBinEmptyNotification(String binType) {
    if (binType == "PLASTIC") {
        plasticBinFullNotified = false;
    } else if (binType == "GLASS") {
        glassBinFullNotified = false;
    } else if (binType == "CAN") {
        canBinFullNotified = false;
    }
}

void sendBinFullNotification(String sensorName, String message) {
    if (WiFi.status() == WL_CONNECTED) {
        HTTPClient http;
        http.begin("http://172.20.10.4/CAPSTONE-MAIN/endpoints/notificationEndpoint.php");   
        http.addHeader("Content-Type", "application/x-www-form-urlencoded");

        String postData = "sensor_name=" + sensorName + "&message=" + message + "&status=unread";
        int httpResponseCode = http.POST(postData);

        Serial.print(sensorName + " full notify response code: ");
        Serial.println(httpResponseCode);

        if (httpResponseCode > 0) {
            String response = http.getString();
            Serial.println("Notification sent: " + response);
        } else {
            Serial.print("Error sending " + sensorName + " notification: ");
            Serial.println(httpResponseCode);
        }
        http.end();
    } else {
        Serial.println("WiFi not connected - notification not sent");
    }
}

// ==================== CLAIM SERVO FUNCTIONS ====================
bool checkClaimServoTrigger() {
    HTTPClient http;
    http.begin(claimServoUrl);
    int httpResponseCode = http.GET();
    if (httpResponseCode > 0) {
        String response = http.getString();
        Serial.println("Claim servo check response: " + response);
        DynamicJsonDocument doc(1024);
        DeserializationError error = deserializeJson(doc, response);
        if (!error && doc.containsKey("trigger")) {
            bool trigger = doc["trigger"];
            if (trigger) {
                int slotNum = doc["slotNum"] | 1;
                Serial.println("CLAIM SERVO TRIGGERED! Slot: " + String(slotNum));
                
                sendToArduino("CLAIM_SERVO:" + String(slotNum));
                
                digitalWrite(BUZZER_PIN, HIGH);
                delay(500);
                digitalWrite(BUZZER_PIN, LOW);
                delay(200);
                digitalWrite(BUZZER_PIN, HIGH);
                delay(500);
                digitalWrite(BUZZER_PIN, LOW);
                
                return true;
            }
        }
    } else {
        Serial.print("Error checking claim servo: "); Serial.println(httpResponseCode);
    }
    http.end();
    return false;
}

// ==================== USER MANAGEMENT FUNCTIONS ====================
bool fetchCurrentUser() {
    HTTPClient http;
    http.begin("http://172.20.10.4/CAPSTONE-MAIN/endpoints/getCurrentUser.php");
    int httpResponseCode = http.GET();
    Serial.println("Fetching current user - HTTP: " + String(httpResponseCode));
    
    if (httpResponseCode > 0) {
        String response = http.getString();
        Serial.println("Response: " + response);
        DynamicJsonDocument doc(1024);
        DeserializationError error = deserializeJson(doc, response);
        
        if (!error) {
            if (doc.containsKey("userID")) {
                userID = doc["userID"];
                username = doc["username"] | "Unknown";
                userIDSet = true;
                Serial.println("User: " + username + " ID: " + String(userID));
                return true;
            } else if (doc.containsKey("error")) {
                Serial.println("Server error: " + String(doc["error"].as<String>()));
            } else {
                Serial.println("No userID in response");
            }
        } else {
            Serial.println("JSON parsing error: " + String(error.c_str()));
        }
    } else {
        Serial.println("HTTP failed: " + String(httpResponseCode));
    }
    http.end();
    return false;
}

// ==================== CONTRIBUTION STATUS FUNCTIONS ====================
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
            Serial.println("Current status: " + String(contributionStarted ? "STARTED" : "STOPPED") + 
                          " | Server status: " + String(newStatus ? "STARTED" : "STOPPED"));
            
            if (newStatus != contributionStarted) {
                contributionStarted = newStatus;
                if (contributionStarted) {
                    Serial.println("CONTRIBUTION STARTED - Ready to accept waste materials!");
                    digitalWrite(BUZZER_PIN, HIGH);
                    delay(200);
                    digitalWrite(BUZZER_PIN, LOW);
                    delay(200);
                    digitalWrite(BUZZER_PIN, HIGH);
                    delay(200);
                    digitalWrite(BUZZER_PIN, LOW);
                } else {
                    Serial.println("CONTRIBUTION STOPPED - Waiting for user to start...");
                }
            } else {
                static unsigned long lastStatusPrint = 0;
                if (millis() - lastStatusPrint > 5000) {
                    lastStatusPrint = millis();
                    Serial.println("Contribution status: " + String(contributionStarted ? "ACTIVE" : "WAITING"));
                }
            }
            return true;
        } else {
            Serial.println("Error parsing contribution status JSON");
        }
    } else {
        Serial.print("Error checking status: "); Serial.println(httpResponseCode);
    }
    http.end();
    return false;
}

// ==================== MATERIAL DETECTION FUNCTIONS ====================
String determineMaterial() {
    int plasticCount = 0;
    int bottleCount = 0;
    int canCount = 0;

    Serial.println("------------------------------------------");
    Serial.println("Material Determination");
    Serial.println("------------------------------------------");
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

    Serial.println("------------------------------------------");
    Serial.println("Plastic: " + String(plasticCount) + " | Glass: " + String(bottleCount) + " | Can: " + String(canCount));
    Serial.println("------------------------------------------");

    if (plasticCount >= PLASTIC_CONFIDENCE_THRESHOLD) return "Plastic Bottles";
    else if (bottleCount >= BOTTLE_CONFIDENCE_THRESHOLD) return "Glass Bottles";
    else if (canCount >= CAN_CONFIDENCE_THRESHOLD) return "Cans";

    return "unknown";
}

// ==================== SYSTEM INITIALIZATION ====================
void setup() {
    Serial.begin(9600);
    Serial2.begin(9600, SERIAL_8N1, ARDUINO_RX, ARDUINO_TX);
    Serial.println("Serial2 initialized RX=" + String(ARDUINO_RX) + " TX=" + String(ARDUINO_TX));
    delay(2000);

    Serial.println("==========================================");
    Serial.println("Initializing WiFi...");
    Serial.println("SSID: " + String(ssid));
    WiFi.begin(ssid, password);
    
    int wifiAttempts = 0;
    while (WiFi.status() != WL_CONNECTED && wifiAttempts < 20) {
        delay(500);
        Serial.print(".");
        wifiAttempts++;
    }
    
    if (WiFi.status() == WL_CONNECTED) {
        Serial.println("");
        Serial.println("WiFi Connected - IP: " + WiFi.localIP().toString());
        Serial.println("==========================================");
    } else {
        Serial.println("");
        Serial.println("WiFi connection failed - Status: " + String(WiFi.status()));
        Serial.println("==========================================");
    }

    fetchCurrentUser();

    Serial.println("==========================================");
    Serial.println("Initializing HX711 sensors...");
    
    // Calibration factors (adjust these based on your load cells)
    float calibration_factor = 1000.0; // Adjust based on your load cell calibration
    
    scalePlastic.begin(DOUT_PLASTIC, SCK_PLASTIC);
    scalePlastic.set_scale(calibration_factor);
    delay(1000);
    scalePlastic.tare(10); // Take 10 readings to zero the scale

    scaleGlass.begin(DOUT_GLASS, SCK_GLASS);
    scaleGlass.set_scale(calibration_factor);
    delay(1000);
    scaleGlass.tare(10);

    scaleCan.begin(DOUT_CAN, SCK_CAN);
    scaleCan.set_scale(calibration_factor);
    delay(1000);
    scaleCan.tare(10);
    
    Serial.println("HX711 sensors initialized and calibrated");

    Serial.println("Initializing servos...");
    servoPlastic.attach(SERVO_PIN1);
    servoGlass.attach(SERVO_PIN2);
    servoCan.attach(SERVO_PIN3);

    servoPlastic.write(0);
    servoGlass.write(0);
    servoCan.write(0);

    pinMode(BUZZER_PIN, OUTPUT);
    digitalWrite(BUZZER_PIN, LOW);

    Serial.println("==========================================");
    Serial.println("Testing Arduino communication...");
    sendToArduino("INIT");
    delay(2000);
    checkArduinoCommunication();
    
    sendToArduino("PING");
    delay(1000);
    checkArduinoCommunication();

    Serial.println("==========================================");
    Serial.println("Setup complete - System ready");
    Serial.println("==========================================");
}

// ==================== MAIN SYSTEM LOOP ====================
void loop() {
    unsigned long currentMillis = millis();

    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("WiFi disconnected - reconnecting...");
        WiFi.begin(ssid, password);
        
        int reconnectAttempts = 0;
        while (WiFi.status() != WL_CONNECTED && reconnectAttempts < 10) {
            delay(1000);
            Serial.print(".");
            reconnectAttempts++;
        }
        
        if (WiFi.status() == WL_CONNECTED) {
            Serial.println("");
            Serial.println("WiFi reconnected - IP: " + WiFi.localIP().toString());
            Serial.println("==========================================");
            fetchCurrentUser();
        } else {
            Serial.println("");
            Serial.println("WiFi reconnection failed");
            Serial.println("==========================================");
            return;
        }
    }

    // Check Arduino Uno communication every 1 second
    if (currentMillis - lastArduinoCheck >= arduinoCheckInterval) {
        lastArduinoCheck = currentMillis;
        checkArduinoCommunication();
        
        static unsigned long lastTestMessage = 0;
        if (millis() - lastTestMessage > 5000) {
            lastTestMessage = millis();
            sendToArduino("TEST");
        }
    }

    // Check for claim servo triggers every 1 second
    if (currentMillis - lastClaimCheck >= claimCheckInterval) {
        lastClaimCheck = currentMillis;
        checkClaimServoTrigger();
    }

    // Check contribution status every 500ms
    if (currentMillis - lastStatusCheck >= statusCheckInterval) {
        lastStatusCheck = currentMillis;
        checkContributionStatus();
    }

    // Only process waste materials if contribution has started
    if (contributionStarted && currentMillis - previousMillis >= interval) {
        previousMillis = currentMillis;
        Serial.println("==========================================");
        Serial.println("Processing waste materials");
        
        String materialType = determineMaterial();
        Serial.println("Material: " + materialType);

        if (materialType != "unknown") {
            digitalWrite(BUZZER_PIN, HIGH);
            delay(500);
            digitalWrite(BUZZER_PIN, LOW);

            float weight = 0;
            if (materialType == "Plastic Bottles") {
                weight = scalePlastic.get_units(5);
                servoPlastic.write(90);
                delay(2000);
                servoPlastic.write(0);
            } else if (materialType == "Glass Bottles") {
                weight = scaleGlass.get_units(5);
                servoGlass.write(90);
                delay(1500);
                servoGlass.write(0);
            } else if (materialType == "Cans") {
                weight = scaleCan.get_units(5);
                servoCan.write(90);
                delay(1000);
                servoCan.write(0);
            }

            // Validate weight reading
            if (weight < 0 || weight > 10000) {
                Serial.println("Invalid weight reading: " + String(weight, 2));
                Serial.println("Skipping submission - recalibrating sensor...");
                
                // Re-tare the sensor for this material type
                if (materialType == "Plastic Bottles") {
                    scalePlastic.tare(10);
                } else if (materialType == "Glass Bottles") {
                    scaleGlass.tare(10);
                } else if (materialType == "Cans") {
                    scaleCan.tare(10);
                }
                return; // Skip this iteration
            }

            Serial.println("Weight: " + String(weight, 2) + " grams");
            Serial.println("------------------------------------------");

            if (userIDSet) {
                HTTPClient http;
                http.begin(serverName);
                http.addHeader("Content-Type", "application/x-www-form-urlencoded");

                String httpRequestData = "material=" + materialType +
                                         "&weight=" + String(weight, 2) +
                                         "&userID=" + String(userID);

                Serial.println("Data: " + httpRequestData);

                int httpResponseCode = http.POST(httpRequestData);
                Serial.println("HTTP: " + String(httpResponseCode));
                if (httpResponseCode > 0) {
                    String response = http.getString();
                    Serial.println("Response: " + response);
                } else {
                    Serial.println("POST error: " + String(httpResponseCode));
                }
                http.end();
            } else {
                Serial.println("User ID not set - fetching user");
                if (fetchCurrentUser()) {
                    Serial.println("User fetched - retrying submission");
                    HTTPClient http;
                    http.begin(serverName);
                    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

                    String httpRequestData = "material=" + materialType +
                                             "&weight=" + String(weight, 2) +
                                             "&userID=" + String(userID);

                    Serial.println("Retry data: " + httpRequestData);

                    int httpResponseCode = http.POST(httpRequestData);
                    Serial.println("HTTP: " + String(httpResponseCode));
                    if (httpResponseCode > 0) {
                        String response = http.getString();
                        Serial.println("Response: " + response);
                    } else {
                        Serial.println("POST error: " + String(httpResponseCode));
                    }
                    http.end();
                } else {
                    Serial.println("Failed to fetch user - skipping submission");
                }
            }
        } else {
            Serial.println("Material not detected");
            Serial.println("==========================================");
        }
    } else if (!contributionStarted) {
        static unsigned long lastWaitingPrint = 0;
        if (millis() - lastWaitingPrint > 10000) {
            lastWaitingPrint = millis();
            Serial.println("Waiting for contribution to start");
        }
        delay(100);
    }
}
