// Arduino Uno Code for Waste Bin Monitoring and Claim Servo Control
// This code monitors ultrasonic sensors for three different bins
// and controls claim servos, communicating with ESP32 via Serial

#include <Servo.h>
#include <SoftwareSerial.h>

// SoftwareSerial for communication with ESP32
// RX=3, TX=2  (matches your wiring)
SoftwareSerial espLink(3, 2); // RX=3, TX=2

// Ultrasonic sensor pins for each bin
// Plastic Bin
#define TRIG_PIN_PLASTIC 4
#define ECHO_PIN_PLASTIC 5

// Glass Bin  
#define TRIG_PIN_GLASS 6
#define ECHO_PIN_GLASS 7

// Can Bin
#define TRIG_PIN_CAN 8
#define ECHO_PIN_CAN 9

// Claim servo pins
#define CLAIM_SERVO_PIN1 11 // Claim servo 1
#define CLAIM_SERVO_PIN2 12  // Claim servo 2
#define CLAIM_SERVO_PIN3 13  // Claim servo 3

// Servo objects
Servo claimServo1;
Servo claimServo2;
Servo claimServo3;

// Bin full threshold distances (in cm) - using hysteresis to prevent false readings
const int BIN_FULL_DISTANCE = 10;    // Distance to trigger "bin full"
const int BIN_EMPTY_DISTANCE = 15;   // Distance to trigger "bin emptied" (higher threshold)

// Bin status flags
bool plasticBinFullNotified = false;
bool glassBinFullNotified = false;
bool canBinFullNotified = false;

// Timing
unsigned long lastCheck = 0;
const long checkInterval = 2000; // Check every 2 seconds

// Communication status
bool esp32Connected = false;
unsigned long lastESP32Message = 0;
const long esp32Timeout = 10000; // 10 seconds timeout

// Function to get distance from ultrasonic sensor with validation
long getDistance(int trigPin, int echoPin) {
    long totalDistance = 0;
    int validReadings = 0;
    
    // Take multiple readings for accuracy
    for (int i = 0; i < 3; i++) {
        digitalWrite(trigPin, LOW);
        delayMicroseconds(2);
        digitalWrite(trigPin, HIGH);
        delayMicroseconds(10);
        digitalWrite(trigPin, LOW);

        long duration = pulseIn(echoPin, HIGH, 30000); // 30ms timeout
        if (duration > 0) {
            long distance = duration * 0.034 / 2; // Convert to cm
            // Only accept reasonable distances (2cm to 400cm)
            if (distance >= 2 && distance <= 400) {
                totalDistance += distance;
                validReadings++;
            }
        }
        delay(10); // Small delay between readings
    }
    
    if (validReadings > 0) {
        return totalDistance / validReadings; // Return average
    } else {
        return 999; // Return invalid reading
    }
}

// Function to check bin status and send notifications
void checkBinStatus(String binType, int trigPin, int echoPin, bool* binFullNotified) {
    long distance = getDistance(trigPin, echoPin);
    
    Serial.print(binType + " bin distance: ");
    Serial.println(distance);
    
    // Check for invalid readings
    if (distance == 999) {
        Serial.println(binType + " bin sensor error - invalid reading");
        return;
    }
    
    if (distance <= BIN_FULL_DISTANCE && !(*binFullNotified)) {
        Serial.println("⚠️ " + binType + " bin is nearly full!");
        Serial.println("📤 Sending BIN_FULL:" + binType + " to ESP32");
        espLink.println("BIN_FULL:" + binType);
        Serial.println("✅ Message sent to ESP32 (ESP32 connected: " + String(esp32Connected ? "YES" : "NO") + ")");
        *binFullNotified = true;
    }
    else if (distance > BIN_EMPTY_DISTANCE && *binFullNotified) {
        Serial.println("✅ " + binType + " bin has been emptied!");
        if (esp32Connected) {
            Serial.println("📤 Sending BIN_EMPTY:" + binType + " to ESP32");
            espLink.println("BIN_EMPTY:" + binType);
        } else {
            Serial.println("❌ ESP32 not connected - notification not sent");
        }
        *binFullNotified = false;
    }
}

// Function to handle claim servo movement
void moveClaimServo(int slotNum) {
    Serial.println("🎯 Moving claim servo for slot " + String(slotNum));
    
    if (slotNum == 1) {
        Serial.println("🔄 Moving claim servo 1 (slot 1) on pin 10...");
        claimServo1.write(90);
        delay(2000); // Hold for 2 seconds
        claimServo1.write(0);
        Serial.println("✅ Servo 1 movement completed");
    } else if (slotNum == 2) {
        Serial.println("🔄 Moving claim servo 2 (slot 2) on pin 11...");
        claimServo2.write(90);
        delay(2000); // Hold for 2 seconds
        claimServo2.write(0);
        Serial.println("✅ Servo 2 movement completed");
    } else if (slotNum == 3) {
        Serial.println("🔄 Moving claim servo 3 (slot 3) on pin 12...");
        claimServo3.write(90);
        delay(2000); // Hold for 2 seconds
        claimServo3.write(0);
        Serial.println("✅ Servo 3 movement completed");
    } else {
        Serial.println("❌ Unknown slot number: " + String(slotNum));
        return;
    }
    
    // Send completion message back to ESP32
    if (esp32Connected) {
        espLink.println("CLAIM_SERVO_COMPLETE:" + String(slotNum));
        Serial.println("📤 Claim servo completion sent to ESP32 for slot " + String(slotNum));
    } else {
        Serial.println("❌ ESP32 not connected - completion not sent");
    }
}

// Function to handle commands from ESP32
void handleESP32Command(String command) {
    lastESP32Message = millis();
    esp32Connected = true;
    
    Serial.println("📨 Received from ESP32: " + command);
    
    if (command == "INIT") {
        Serial.println("✅ Arduino Uno initialized - Bin monitoring and claim servo control started");
        espLink.println("ARDUINO_READY");
        Serial.println("📤 ARDUINO_READY sent to ESP32");
    }
    else if (command == "STATUS") {
        // Send current status of all bins
        espLink.println("STATUS:PLASTIC:" + String(plasticBinFullNotified ? "FULL" : "EMPTY"));
        espLink.println("STATUS:GLASS:" + String(glassBinFullNotified ? "FULL" : "EMPTY"));
        espLink.println("STATUS:CAN:" + String(canBinFullNotified ? "FULL" : "EMPTY"));
        Serial.println("📤 Status sent to ESP32");
    }
    else if (command.startsWith("CLAIM_SERVO:")) {
        int slotNum = command.substring(12).toInt();
        moveClaimServo(slotNum);
    }
    else if (command == "PING") {
        espLink.println("PONG");
        Serial.println("📤 Sent PONG to ESP32");
    }
    else if (command == "TEST") {
        espLink.println("TEST_RESPONSE");
        Serial.println("📤 Sent TEST_RESPONSE to ESP32");
    }
    else {
        Serial.println("❓ Unknown command from ESP32: " + command);
    }
}

void setup() {
    // Initialize serial communication
    Serial.begin(9600);       // USB Serial Monitor
    espLink.begin(9600);      // UART link to ESP32
    
    Serial.println("Uno ready – waiting for ESP32...");
    
    // Initialize ultrasonic sensor pins
    pinMode(TRIG_PIN_PLASTIC, OUTPUT);
    pinMode(ECHO_PIN_PLASTIC, INPUT);
    pinMode(TRIG_PIN_GLASS, OUTPUT);
    pinMode(ECHO_PIN_GLASS, INPUT);
    pinMode(TRIG_PIN_CAN, OUTPUT);
    pinMode(ECHO_PIN_CAN, INPUT);
    
    // Initialize all trigger pins to LOW
    digitalWrite(TRIG_PIN_PLASTIC, LOW);
    digitalWrite(TRIG_PIN_GLASS, LOW);
    digitalWrite(TRIG_PIN_CAN, LOW);
    
    // Initialize claim servos
    claimServo1.attach(CLAIM_SERVO_PIN1);
    claimServo2.attach(CLAIM_SERVO_PIN2);
    claimServo3.attach(CLAIM_SERVO_PIN3);
    
    // Initialize servos to 0 position
    claimServo1.write(0);
    claimServo2.write(0);
    claimServo3.write(0);
    
    Serial.println("✅ Arduino Uno Bin Monitor and Claim Servo Controller Started");
    Serial.println("📡 Listening for ESP32 commands on pins 3(RX) and 2(TX)");
    
    delay(1000); // Give time for ESP32 to initialize
}

void loop() {
    unsigned long currentMillis = millis();
    
    // Check for commands from ESP32
    if (espLink.available()) {
        String command = espLink.readStringUntil('\n');
        command.trim();
        Serial.print("Got from ESP32: ");
        Serial.println(command);
        handleESP32Command(command);
    }
    
    // Check if ESP32 connection is still alive
    if (esp32Connected && (currentMillis - lastESP32Message > esp32Timeout)) {
        esp32Connected = false;
        Serial.println("⚠️ ESP32 connection timeout - waiting for reconnection");
    }
    
    // Check bin status every 2 seconds
    if (currentMillis - lastCheck >= checkInterval) {
        lastCheck = currentMillis;
        
        Serial.println("🔍 Checking bin status...");
        
        // Check each bin
        checkBinStatus("PLASTIC", TRIG_PIN_PLASTIC, ECHO_PIN_PLASTIC, &plasticBinFullNotified);
        delay(100); // Small delay between sensors
        
        checkBinStatus("GLASS", TRIG_PIN_GLASS, ECHO_PIN_GLASS, &glassBinFullNotified);
        delay(100);
        
        checkBinStatus("CAN", TRIG_PIN_CAN, ECHO_PIN_CAN, &canBinFullNotified);
        delay(100);
        
        // Show connection status
        if (esp32Connected) {
            Serial.println("✅ ESP32 connected");
        } else {
            Serial.println("❌ ESP32 disconnected");
        }
    }
    
    // Small delay to prevent overwhelming the system
    delay(50);
}