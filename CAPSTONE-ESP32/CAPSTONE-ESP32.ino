#include <ESP32Servo.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <HX711.h>

// WiFi credentials
const char* ssid = "iPhone";
const char* password = "gelo123456789";
const char* serverName = "http://172.20.10.2/CAPSTONE-MAIN/endpoints/endpoint.php";
const char* statusCheckUrl = "http://172.20.10.2/CAPSTONE-MAIN/endpoints/checkContributionStatus.php";
const char* claimServoUrl = "http://172.20.10.2/CAPSTONE-MAIN/endpoints/checkClaimServo.php";

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
const int ARDUINO_RX = 17; // ESP32 TX to Arduino RX (green)
const int ARDUINO_TX = 16; // ESP32 RX to Arduino TX (orange)

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
unsigned long lastClaimCheck = 0;
const long claimCheckInterval = 1000; // Check for claim triggers every 1 second
unsigned long lastArduinoCheck = 0;
const long arduinoCheckInterval = 1000; // Check Arduino communication every 1 second

// Communication protocol with Arduino Uno
void sendToArduino(String command) {
    Serial2.println(command);
    Serial.println("Sent to Arduino: " + command);
}

void checkArduinoCommunication() {
    if (Serial2.available()) {
        String arduinoMessage = Serial2.readStringUntil('\n');
        arduinoMessage.trim();
        Serial.println("Received from Arduino: " + arduinoMessage);
        
        // Parse bin status messages from Arduino
        if (arduinoMessage.startsWith("BIN_FULL:")) {
            String binType = arduinoMessage.substring(9);
            handleBinFullNotification(binType);
        } else if (arduinoMessage.startsWith("BIN_EMPTY:")) {
            String binType = arduinoMessage.substring(10);
            handleBinEmptyNotification(binType);
        } else if (arduinoMessage.startsWith("CLAIM_SERVO_COMPLETE:")) {
            String slotNum = arduinoMessage.substring(20);
            Serial.println("Claim servo movement completed for slot " + slotNum + "!");
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
        http.begin("http://172.20.10.2/CAPSTONE-MAIN/endpoints/notifEndpoint.php");   
        http.addHeader("Content-Type", "application/x-www-form-urlencoded");

        String postData = "sensor_name=" + sensorName + "&message=" + message + "&status=unread";
        int httpResponseCode = http.POST(postData);

        Serial.print(sensorName + " full notify response code: ");
        Serial.println(httpResponseCode);

        if (httpResponseCode > 0) {
            String response = http.getString();
            Serial.println(response);
        } else {
            Serial.print("Error in sending " + sensorName + " full request: ");
            Serial.println(httpResponseCode);
        }
        http.end();
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
    Serial2.begin(9600, SERIAL_8N1, ARDUINO_RX, ARDUINO_TX); // Initialize Serial2 for Arduino communication
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
    sendToArduino("INIT");

    Serial.println("Setup complete");
    Serial.println("Waiting for user to start contributing...");
    Serial.println("Claim servo ready - monitoring for reward claims...");
    Serial.println("Arduino Uno communication initialized");
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
    }

    // Check for claim servo triggers every 1 second
    if (currentMillis - lastClaimCheck >= claimCheckInterval) {
        lastClaimCheck = currentMillis;
        checkClaimServoTrigger();
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
}
