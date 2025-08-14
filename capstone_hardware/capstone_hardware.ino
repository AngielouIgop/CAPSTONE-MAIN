#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

// WiFi credentials
const char* ssid = "PLDTHOMEFIBR726d0";
const char* password = "PLDTWIFI2f7xd";
const char* serverName = "http://192.168.1.13/CAPSTONEE/endpoint.php";

// User info
int userID = 0;
bool userIDSet = false;
String username = "";

// Sensor pins
const int SENSOR_PIN_1 = 34; // plastic bottle
const int SENSOR_PIN_2 = 35; // glass bottle
const int SENSOR_PIN_3 = 32; // tin cans

// Buzzer pin
const int BUZZER_PIN = 33; // adjust according to your wiring

// Reading validation
const int NUM_READINGS = 10;
const int READING_DELAY = 50;

// Thresholds
const int PLASTIC_THRESHOLD_MIN = 44;
const int PLASTIC_THRESHOLD_MAX = 100;
const int BOTTLE_THRESHOLD_MIN = 28;
const int BOTTLE_THRESHOLD_MAX = 41;
const int CAN_THRESHOLD_MIN = 8;
const int CAN_THRESHOLD_MAX = 27;
const int CONFIDENCE_THRESHOLD = 7;

// Timing for main loop
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
        Serial.print("Error on fetching current user: "); Serial.println(httpResponseCode);
        Serial.print("Error details: "); Serial.println(http.errorToString(httpResponseCode));
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

        Serial.print("Plastic Reading "); Serial.print(i); Serial.print(": "); Serial.println(plasticReading);
        Serial.print("Glass Reading "); Serial.print(i); Serial.print(": "); Serial.println(bottleReading);
        Serial.print("Can Reading "); Serial.print(i); Serial.print(": "); Serial.println(canReading);

        if (plasticReading >= PLASTIC_THRESHOLD_MIN && plasticReading <= PLASTIC_THRESHOLD_MAX)
            plasticCount++;
        if (bottleReading >= BOTTLE_THRESHOLD_MIN && bottleReading <= BOTTLE_THRESHOLD_MAX)
            bottleCount++;
        if (canReading >= CAN_THRESHOLD_MIN && canReading <= CAN_THRESHOLD_MAX)
            canCount++;

        delay(READING_DELAY);
    }

    Serial.print("Plastic count: "); Serial.println(plasticCount);
    Serial.print("Bottle count: "); Serial.println(bottleCount);
    Serial.print("Can count: "); Serial.println(canCount);

    if (plasticCount >= CONFIDENCE_THRESHOLD) return "Plastic Bottles";
    else if (bottleCount >= CONFIDENCE_THRESHOLD) return "Glass Bottles";
    else if (canCount >= CONFIDENCE_THRESHOLD) return "Cans";
    return "unknown";
}

void setup() {
    Serial.begin(9600);
    delay(2000);
    Serial.println("=== SETUP STARTING ===");

    pinMode(BUZZER_PIN, OUTPUT);
    digitalWrite(BUZZER_PIN, LOW);

    WiFi.begin(ssid, password);
    Serial.print("Connecting to WiFi");
    int attempts = 0;
    while (WiFi.status() != WL_CONNECTED && attempts < 20) {
        delay(500);
        Serial.print(".");
        attempts++;
    }
    Serial.println();

    if (WiFi.status() == WL_CONNECTED) {
        Serial.print("Connected to SSID: "); Serial.println(WiFi.SSID());
        Serial.print("ESP32 IP Address: "); Serial.println(WiFi.localIP());
        fetchCurrentUser();
    } else {
        Serial.println("Failed to connect to WiFi");
    }

    Serial.println("Calibrating sensors (waiting for 1 second)...");
    unsigned long startTime = millis();
    while (millis() - startTime < 1000) {
        analogRead(SENSOR_PIN_1);
        analogRead(SENSOR_PIN_2);
        analogRead(SENSOR_PIN_3);
        delay(10);
    }

    int initialPlastic = getAverageReading(SENSOR_PIN_1);
    int initialGlass = getAverageReading(SENSOR_PIN_2);
    int initialCan = getAverageReading(SENSOR_PIN_3);
    Serial.print("Initial Plastic sensor reading: "); Serial.println(initialPlastic);
    Serial.print("Initial Glass sensor reading: "); Serial.println(initialGlass);
    Serial.print("Initial Can sensor reading: "); Serial.println(initialCan);
    Serial.println("=== SETUP COMPLETE ===");
}

void loop() {
    unsigned long currentMillis = millis();

    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("WiFi Disconnected. Attempting to reconnect...");
        WiFi.begin(ssid, password);
        delay(5000);
        if (WiFi.status() == WL_CONNECTED) {
            Serial.println("WiFi Reconnected!");
            fetchCurrentUser();
        }
        return;
    }

    if (currentMillis - previousMillis >= interval) {
        previousMillis = currentMillis;

        Serial.println("\n--- Initiating Sensor Scan ---");
        int plasticValue = getAverageReading(SENSOR_PIN_1);
        int glassValue = getAverageReading(SENSOR_PIN_2);
        int canValue = getAverageReading(SENSOR_PIN_3);
        Serial.print("Average Plastic Sensor Value: "); Serial.println(plasticValue);
        Serial.print("Average Glass Sensor Value: "); Serial.println(glassValue);
        Serial.print("Average Can Sensor Value: "); Serial.println(canValue);

        String materialType = determineMaterial();
        Serial.print("Detected Material: "); Serial.println(materialType);

        if (materialType != "unknown") {
            digitalWrite(BUZZER_PIN, HIGH);
            delay(500);
            digitalWrite(BUZZER_PIN, LOW);
        }

        int sensorValue = 0;
        if (materialType == "Plastic Bottles") sensorValue = plasticValue;
        else if (materialType == "Glass Bottles") sensorValue = glassValue;
        else if (materialType == "Cans") sensorValue = canValue;

        if (materialType != "unknown" && userIDSet) {
            HTTPClient http;
            Serial.print("Connecting to server: "); Serial.println(serverName);
            http.begin(serverName);
            http.addHeader("Content-Type", "application/x-www-form-urlencoded");
            String httpRequestData = "sensor_value=" + String(sensorValue) +
                                     "&material=" + materialType +
                                     "&userID=" + String(userID);
            Serial.print("Sending data: "); Serial.println(httpRequestData);
            int httpResponseCode = http.POST(httpRequestData);
            Serial.print("HTTP Response code: "); Serial.println(httpResponseCode);
            if (httpResponseCode > 0) {
                String response = http.getString();
                Serial.println("Response: " + response);
                if (response.indexOf("Success") >= 0)
                    Serial.println("Data successfully saved to database");
                else if (response.indexOf("Error") >= 0)
                    Serial.println("Error saving to database: " + response);
            } else {
                Serial.print("Error on sending POST: "); Serial.println(httpResponseCode);
                Serial.print("Error details: "); Serial.println(http.errorToString(httpResponseCode));
            }
            http.end();
        } else if (materialType == "unknown") {
            Serial.println("Material not identified with confidence.");
        } else {
            Serial.println("User ID not set. Cannot send data.");
            fetchCurrentUser();
        }
    }
}
