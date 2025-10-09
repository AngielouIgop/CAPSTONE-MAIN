#include <ESP32Servo.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <HX711.h>

// WiFi credentials
const char* ssid = "iPhone";
const char* password = "gelo123456789";
const char* serverName = "http://172.20.10.3/CAPSTONE-MAIN/endpoints/endpoint.php";
const char* statusCheckUrl = "http://172.20.10.3/CAPSTONE-MAIN/endpoints/checkContributionStatus.php";
const char* claimServoUrl = "http://172.20.10.3/CAPSTONE-MAIN/endpoints/checkClaimServo.php";

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
// Claim servos moved to Arduino Uno

const int SERVO_PIN1 = 23; // Plastic
const int SERVO_PIN2 = 22; // Glass
const int SERVO_PIN3 = 21; // Can

// Buzzer pin
const int BUZZER_PIN = 5;

// Communication with Arduino Uno
const int ARDUINO_RX = 16; // ESP32 RX pin (receives from Arduino TX)
const int ARDUINO_TX = 17; // ESP32 TX pin (sends to Arduino RX)

// Bin full flags (received from Arduino Uno)
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
const int PLASTIC_THRESHOLD_MIN = 70;
const int PLASTIC_THRESHOLD_MAX = 100;
const int BOTTLE_THRESHOLD_MIN = 1;
const int BOTTLE_THRESHOLD_MAX = 39;
const int CAN_THRESHOLD_MIN = 40;
const int CAN_THRESHOLD_MAX = 69;
const int PLASTIC_CONFIDENCE_THRESHOLD = 6;
const int BOTTLE_CONFIDENCE_THRESHOLD = 5;
const int CAN_CONFIDENCE_THRESHOLD = 5;

// Timing
unsigned long previousMillis = 0;
const long interval = 5000; // 5 seconds
unsigned long lastStatusCheck = 0;
const long statusCheckInterval = 500; // Check contribution status every 500ms (PRIORITY)
unsigned long lastClaimCheck = 0;
const long claimCheckInterval = 1000; // Check for claim triggers every 1 second
unsigned long lastArduinoCheck = 0;
const long arduinoCheckInterval = 1000; // Check Arduino communication every 1 second

// Communication protocol with Arduino Uno
void sendToArduino(String command) {
    Serial2.println(command);
    Serial.println("📤 Sent to Arduino: " + command);
    Serial.println("📡 Serial2 status - Available: " + String(Serial2.available()));
}

void checkArduinoCommunication() {
    if (Serial2.available()) {
        String arduinoMessage = Serial2.readStringUntil('\n');
        arduinoMessage.trim();
        Serial.println("📨 Received from Arduino: " + arduinoMessage);
        
        // Parse messages from Arduino
        if (arduinoMessage.startsWith("ARDUINO_READY")) {
            Serial.println("✅ Arduino Uno is ready and responding!");
        }
        else if (arduinoMessage.startsWith("CLAIM_SERVO_COMPLETE:")) {
            String slotNum = arduinoMessage.substring(20);
            Serial.println("✅ Claim servo movement completed for slot " + slotNum + "!");
        }
        else if (arduinoMessage.startsWith("BIN_FULL:")) {
            String binType = arduinoMessage.substring(9);
            Serial.println("⚠️ Bin full notification from Arduino: " + binType);
            handleBinFullNotification(binType);
        }
        else if (arduinoMessage.startsWith("BIN_EMPTY:")) {
            String binType = arduinoMessage.substring(10);
            Serial.println("✅ Bin empty notification from Arduino: " + binType);
            handleBinEmptyNotification(binType);
        }
        else if (arduinoMessage == "PONG") {
            Serial.println("🏓 PONG received from Arduino - Communication working!");
        }
        else if (arduinoMessage == "TEST_RESPONSE") {
            Serial.println("✅ TEST_RESPONSE received from Arduino - Communication working!");
        }
        else {
            Serial.println("❓ Unknown message from Arduino: " + arduinoMessage);
        }
    }
}

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
        http.begin("http://172.20.10.3/CAPSTONE-MAIN/endpoints/notificationEndpoint.php");   
        http.addHeader("Content-Type", "application/x-www-form-urlencoded");

        String postData = "sensor_name=" + sensorName + "&message=" + message + "&status=unread";
        int httpResponseCode = http.POST(postData);

        Serial.print(sensorName + " full notify response code: ");
        Serial.println(httpResponseCode);

        if (httpResponseCode > 0) {
            String response = http.getString();
            Serial.println("📤 Notification sent to database: " + response);
        } else {
            Serial.print("❌ Error sending " + sensorName + " notification: ");
            Serial.println(httpResponseCode);
        }
        http.end();
    } else {
        Serial.println("❌ WiFi not connected - notification not sent to database");
    }
}

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
                int slotNum = doc["slotNum"] | 1; // Default to slot 1 if not specified
                Serial.println("CLAIM SERVO TRIGGERED! Slot: " + String(slotNum));
                
                // Send claim servo command to Arduino Uno
                sendToArduino("CLAIM_SERVO:" + String(slotNum));
                
                // Buzzer indication
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

bool fetchCurrentUser() {
    HTTPClient http;
    http.begin("http://172.20.10.3/CAPSTONE-MAIN/endpoints/getCurrentUser.php");
    int httpResponseCode = http.GET();
    Serial.println("🔍 Fetching current user...");
    Serial.println("📡 HTTP Response code: " + String(httpResponseCode));
    
    if (httpResponseCode > 0) {
        String response = http.getString();
        Serial.println("📨 Response: " + response);
        DynamicJsonDocument doc(1024);
        DeserializationError error = deserializeJson(doc, response);
        
        if (!error) {
            if (doc.containsKey("userID")) {
                userID = doc["userID"];
                username = doc["username"] | "Unknown";
                userIDSet = true;
                Serial.println("✅ Current user: " + username);
                Serial.println("✅ UserID: " + String(userID));
                return true;
            } else if (doc.containsKey("error")) {
                Serial.println("❌ Error from server: " + String(doc["error"].as<String>()));
            } else {
                Serial.println("❌ No userID or error field in response");
            }
        } else {
            Serial.println("❌ JSON parsing error: " + String(error.c_str()));
        }
    } else {
        Serial.println("❌ HTTP request failed with code: " + String(httpResponseCode));
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
        Serial.println("🔍 Status check response: " + response);
        DynamicJsonDocument doc(1024);
        DeserializationError error = deserializeJson(doc, response);
        if (!error && doc.containsKey("contribution_started")) {
            bool newStatus = doc["contribution_started"];
            Serial.println("📊 Current status: " + String(contributionStarted ? "STARTED" : "STOPPED") + 
                          " | Server status: " + String(newStatus ? "STARTED" : "STOPPED"));
            
            if (newStatus != contributionStarted) {
                contributionStarted = newStatus;
                if (contributionStarted) {
                    Serial.println("🚀 CONTRIBUTION STARTED - Ready to accept waste materials!");
                    // Activate buzzer to indicate ready
                    digitalWrite(BUZZER_PIN, HIGH);
                    delay(200);
                    digitalWrite(BUZZER_PIN, LOW);
                    delay(200);
                    digitalWrite(BUZZER_PIN, HIGH);
                    delay(200);
                    digitalWrite(BUZZER_PIN, LOW);
                } else {
                    Serial.println("⏸️ CONTRIBUTION STOPPED - Waiting for user to start...");
                }
            } else {
                // Show current status periodically
                static unsigned long lastStatusPrint = 0;
                if (millis() - lastStatusPrint > 5000) { // Every 5 seconds
                    lastStatusPrint = millis();
                    Serial.println("📊 Contribution status: " + String(contributionStarted ? "ACTIVE" : "WAITING"));
                }
            }
            return true;
        } else {
            Serial.println("❌ Error parsing contribution status JSON");
        }
    } else {
        Serial.print("❌ Error checking status: "); Serial.println(httpResponseCode);
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
    Serial2.begin(9600, SERIAL_8N1, ARDUINO_RX, ARDUINO_TX); // Initialize Serial2 for Arduino communication
    Serial.println("🔧 Serial2 initialized on pins RX=" + String(ARDUINO_RX) + ", TX=" + String(ARDUINO_TX));
    delay(2000);

    // Initialize WiFi
    Serial.println("Attempting to connect to WiFi...");
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
        Serial.println("WiFi Connected!");
        Serial.print("IP address: ");
        Serial.println(WiFi.localIP());
    } else {
        Serial.println("");
        Serial.println("WiFi connection failed!");
        Serial.println("WiFi Status: " + String(WiFi.status()));
        Serial.println("Please check your WiFi credentials and network availability.");
    }

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
    // Claim servos are now controlled by Arduino Uno

    servoPlastic.write(0);
    servoGlass.write(0);
    servoCan.write(0);

    pinMode(BUZZER_PIN, OUTPUT);
    digitalWrite(BUZZER_PIN, LOW);

    // Send initialization command to Arduino Uno
    Serial.println("🔄 Sending INIT command to Arduino Uno...");
    sendToArduino("INIT");
    
    // Wait for Arduino response
    delay(2000);
    checkArduinoCommunication();
    
    // Test communication with ping-pong
    Serial.println("🔄 Testing communication with PING...");
    sendToArduino("PING");
    delay(1000);
    checkArduinoCommunication();

    Serial.println("✅ Setup complete");
    Serial.println("⏳ Waiting for user to start contributing...");
    Serial.println("🎯 Claim servo ready - monitoring for reward claims...");
    Serial.println("🔗 Arduino Uno communication initialized");
}

void loop() {
    unsigned long currentMillis = millis();

    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("WiFi disconnected, attempting reconnect...");
        WiFi.begin(ssid, password);
        
        int reconnectAttempts = 0;
        while (WiFi.status() != WL_CONNECTED && reconnectAttempts < 10) {
            delay(1000);
            Serial.print(".");
            reconnectAttempts++;
        }
        
        if (WiFi.status() == WL_CONNECTED) {
            Serial.println("");
            Serial.println("WiFi reconnected!");
            Serial.print("New IP address: ");
            Serial.println(WiFi.localIP());
            fetchCurrentUser();
        } else {
            Serial.println("");
            Serial.println("WiFi reconnection failed!");
            return;
        }
    }

    // Check Arduino Uno communication every 1 second
    if (currentMillis - lastArduinoCheck >= arduinoCheckInterval) {
        lastArduinoCheck = currentMillis;
        checkArduinoCommunication();
        
        // Send a test message every 5 seconds
        static unsigned long lastTestMessage = 0;
        if (millis() - lastTestMessage > 5000) {
            lastTestMessage = millis();
            Serial.println("🔄 Sending test message to Arduino...");
            sendToArduino("TEST");
        }
    }

    // Check for claim servo triggers every 1 second
    if (currentMillis - lastClaimCheck >= claimCheckInterval) {
        lastClaimCheck = currentMillis;
        checkClaimServoTrigger();
    }

    // PRIORITY: Check contribution status every 500ms (most important)
    if (currentMillis - lastStatusCheck >= statusCheckInterval) {
        lastStatusCheck = currentMillis;
        checkContributionStatus();
    }

    // Only process waste materials if contribution has started
    if (contributionStarted && currentMillis - previousMillis >= interval) {
        previousMillis = currentMillis;
        Serial.println("🔄 Processing waste materials - Contribution is ACTIVE");
        
        String materialType = determineMaterial();
        Serial.print("🔍 Detected Material: "); Serial.println(materialType);

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

                Serial.print("📤 Sending data: "); Serial.println(httpRequestData);

                int httpResponseCode = http.POST(httpRequestData);
                Serial.print("📡 HTTP Response code: "); Serial.println(httpResponseCode);
                if (httpResponseCode > 0) {
                    String response = http.getString();
                    Serial.println("📨 Response: " + response);
                } else {
                    Serial.print("❌ POST error: "); Serial.println(httpResponseCode);
                }
                http.end();
            } else {
                Serial.println("❌ User ID not set, attempting to fetch user...");
                if (fetchCurrentUser()) {
                    Serial.println("✅ User fetched successfully, retrying data submission...");
                    // Retry the data submission
                    HTTPClient http;
                    http.begin(serverName);
                    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

                    String httpRequestData = "material=" + materialType +
                                             "&weight=" + String(weight, 2) +
                                             "&userID=" + String(userID);

                    Serial.print("📤 Retrying data submission: "); Serial.println(httpRequestData);

                    int httpResponseCode = http.POST(httpRequestData);
                    Serial.print("📡 HTTP Response code: "); Serial.println(httpResponseCode);
                    if (httpResponseCode > 0) {
                        String response = http.getString();
                        Serial.println("📨 Response: " + response);
                    } else {
                        Serial.print("❌ POST error: "); Serial.println(httpResponseCode);
                    }
                    http.end();
                } else {
                    Serial.println("❌ Failed to fetch user, skipping data submission");
                }
            }
        } else {
            Serial.println("Material not detected. Servo will not move. Buzzer will remain silent.");
        }
    } else if (!contributionStarted) {
        // Show waiting status periodically
        static unsigned long lastWaitingPrint = 0;
        if (millis() - lastWaitingPrint > 10000) { // Every 10 seconds
            lastWaitingPrint = millis();
            Serial.println("⏳ Waiting for contribution to start...");
        }
        // Small delay to reduce CPU usage
        delay(100);
    }
}