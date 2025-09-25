// Arduino Uno Code for Waste Bin Monitoring and Claim Servo Control
// This code monitors ultrasonic sensors for three different bins
// and controls claim servos, communicating with ESP32 via Serial

#include <Servo.h>
#include <SoftwareSerial.h>

// SoftwareSerial for communication with ESP32
// RX pin (Arduino receives from ESP32 TX)
// TX pin (Arduino sends to ESP32 RX)
SoftwareSerial espSerial(2, 3); // Use pins 2 and 3 for ESP32 communication

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

// Bin full threshold distance (in cm)
const int BIN_FULL_DISTANCE = 10;

// Bin status flags
bool plasticBinFullNotified = false;
bool glassBinFullNotified = false;
bool canBinFullNotified = false;

// Timing
unsigned long lastCheck = 0;
const long checkInterval = 2000; // Check every 2 seconds

// Function to get distance from ultrasonic sensor
long getDistance(int trigPin, int echoPin) {
    digitalWrite(trigPin, LOW);
    delayMicroseconds(2);
    digitalWrite(trigPin, HIGH);
    delayMicroseconds(10);
    digitalWrite(trigPin, LOW);

    long duration = pulseIn(echoPin, HIGH);
    long distance = duration * 0.034 / 2; // Convert to cm
    return distance;
}

// Function to check bin status and send notifications
void checkBinStatus(String binType, int trigPin, int echoPin, bool* binFullNotified) {
    long distance = getDistance(trigPin, echoPin);
    
    Serial.print(binType + " bin distance: ");
    Serial.println(distance);
    
    if (distance <= BIN_FULL_DISTANCE && !(*binFullNotified)) {
        Serial.println(binType + " bin is nearly full!");
        espSerial.println("BIN_FULL:" + binType);
        *binFullNotified = true;
    }
    else if (distance > BIN_FULL_DISTANCE && *binFullNotified) {
        Serial.println(binType + " bin has been emptied!");
        espSerial.println("BIN_EMPTY:" + binType);
        *binFullNotified = false;
    }
}

// Function to handle claim servo movement
void moveClaimServo(int slotNum) {
    Serial.println("Moving claim servo for slot " + String(slotNum));
    
    if (slotNum == 1) {
        Serial.println("Moving claim servo 1 (slot 1) on pin 10...");
        claimServo1.write(90);
        delay(2000); // Hold for 2 seconds
        claimServo1.write(0);
        Serial.println("Servo 1 movement completed");
    } else if (slotNum == 2) {
        Serial.println("Moving claim servo 2 (slot 2) on pin 11...");
        claimServo2.write(90);
        delay(2000); // Hold for 2 seconds
        claimServo2.write(0);
        Serial.println("Servo 2 movement completed");
    } else if (slotNum == 3) {
        Serial.println("Moving claim servo 3 (slot 3) on pin 12...");
        claimServo3.write(90);
        delay(2000); // Hold for 2 seconds
        claimServo3.write(0);
        Serial.println("Servo 3 movement completed");
    } else {
        Serial.println("Unknown slot number: " + String(slotNum));
        return;
    }
    
    // Send completion message back to ESP32
    espSerial.println("CLAIM_SERVO_COMPLETE:" + String(slotNum));
    Serial.println("Claim servo movement completed for slot " + String(slotNum) + "!");
}

// Function to handle commands from ESP32
void handleESP32Command(String command) {
    if (command == "INIT") {
        Serial.println("Arduino Uno initialized - Bin monitoring and claim servo control started");
        espSerial.println("ARDUINO_READY");
    }
    else if (command == "STATUS") {
        // Send current status of all bins
        espSerial.println("STATUS:PLASTIC:" + String(plasticBinFullNotified ? "FULL" : "EMPTY"));
        espSerial.println("STATUS:GLASS:" + String(glassBinFullNotified ? "FULL" : "EMPTY"));
        espSerial.println("STATUS:CAN:" + String(canBinFullNotified ? "FULL" : "EMPTY"));
    }
    else if (command.startsWith("CLAIM_SERVO:")) {
        int slotNum = command.substring(12).toInt();
        moveClaimServo(slotNum);
    }
}

void setup() {
    // Initialize serial communication
    Serial.begin(9600);
    espSerial.begin(9600); // Communication with ESP32
    
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
    
    Serial.println("Arduino Uno Bin Monitor and Claim Servo Controller Started");
    Serial.println("Waiting for ESP32 initialization...");
    
    delay(1000); // Give time for ESP32 to initialize
}

void loop() {
    unsigned long currentMillis = millis();
    
    // Check for commands from ESP32
    if (espSerial.available()) {
        String command = espSerial.readStringUntil('\n');
        command.trim();
        Serial.println("Received from ESP32: " + command);
        handleESP32Command(command);
    }
    
    // Check bin status every 2 seconds
    if (currentMillis - lastCheck >= checkInterval) {
        lastCheck = currentMillis;
        
        // Check each bin
        checkBinStatus("PLASTIC", TRIG_PIN_PLASTIC, ECHO_PIN_PLASTIC, &plasticBinFullNotified);
        delay(100); // Small delay between sensors
        
        checkBinStatus("GLASS", TRIG_PIN_GLASS, ECHO_PIN_GLASS, &glassBinFullNotified);
        delay(100);
        
        checkBinStatus("CAN", TRIG_PIN_CAN, ECHO_PIN_CAN, &canBinFullNotified);
        delay(100);
    }
    
    // Small delay to prevent overwhelming the system
    delay(50);
}
