// Arduino Uno Code for Waste Bin Monitoring and Claim Servo Control
// Memory-optimized version for Arduino Uno

#include <Servo.h>
#include <SoftwareSerial.h>

// SoftwareSerial for communication with ESP32
SoftwareSerial espLink(3, 2); // RX=3, TX=2

// Ultrasonic sensor pins
#define TRIG_PIN_PLASTIC 4
#define ECHO_PIN_PLASTIC 5
#define TRIG_PIN_GLASS 6
#define ECHO_PIN_GLASS 7
#define TRIG_PIN_CAN 8
#define ECHO_PIN_CAN 9

// Claim servo pins
#define CLAIM_SERVO_PIN1 11
#define CLAIM_SERVO_PIN2 12
#define CLAIM_SERVO_PIN3 13

// Buzzer pin
#define BUZZER_PIN 10

// Servo objects
Servo claimServo1;
Servo claimServo2;
Servo claimServo3;

// Constants
const int BIN_FULL_DISTANCE = 10;
const int BIN_EMPTY_DISTANCE = 15;
const long checkInterval = 2000;
const long esp32Timeout = 10000;

// Variables
bool plasticBinFullNotified = false;
bool glassBinFullNotified = false;
bool canBinFullNotified = false;
bool esp32Connected = false;
unsigned long lastCheck = 0;
unsigned long lastESP32Message = 0;
bool buzzerActive = false;
unsigned long lastBuzzerToggle = 0;
const long buzzerInterval = 500; // Buzzer beep interval (500ms on/off)

// Function to get distance from ultrasonic sensor
long getDistance(int trigPin, int echoPin) {
    long totalDistance = 0;
    int validReadings = 0;
    
    for (int i = 0; i < 3; i++) {
        digitalWrite(trigPin, LOW);
        delayMicroseconds(2);
        digitalWrite(trigPin, HIGH);
        delayMicroseconds(10);
        digitalWrite(trigPin, LOW);

        long duration = pulseIn(echoPin, HIGH, 30000);
        if (duration > 0) {
            long distance = duration * 0.034 / 2;
            if (distance >= 2 && distance <= 400) {
                totalDistance += distance;
                validReadings++;
            }
        }
        delay(10);
    }
    
    return validReadings > 0 ? totalDistance / validReadings : 999;
}

// Function to control buzzer
void updateBuzzer() {
    // Check if any bin is full
    bool anyBinFull = plasticBinFullNotified || glassBinFullNotified || canBinFullNotified;
    
    if (anyBinFull) {
        // Activate buzzer with beeping pattern
        unsigned long currentMillis = millis();
        if (currentMillis - lastBuzzerToggle >= buzzerInterval) {
            lastBuzzerToggle = currentMillis;
            buzzerActive = !buzzerActive;
            digitalWrite(BUZZER_PIN, buzzerActive ? HIGH : LOW);
        }
    } else {
        // Turn off buzzer if all bins are empty
        if (buzzerActive) {
            buzzerActive = false;
            digitalWrite(BUZZER_PIN, LOW);
        }
    }
}

// Function to check bin status
void checkBinStatus(char binType, int trigPin, int echoPin, bool* binFullNotified) {
    long distance = getDistance(trigPin, echoPin);
    
    Serial.print(binType);
    Serial.print(" bin: ");
    Serial.println(distance);
    
    if (distance == 999) {
        Serial.println("Sensor error");
        return;
    }
    
    if (distance <= BIN_FULL_DISTANCE && !(*binFullNotified)) {
        Serial.print("Bin ");
        Serial.print(binType);
        Serial.println(" FULL!");
        espLink.print("BIN_FULL:");
        espLink.println(binType == 'P' ? "PLASTIC" : (binType == 'G' ? "GLASS" : "CAN"));
        *binFullNotified = true;
        // Buzzer will be activated in updateBuzzer() function
    }
    else if (distance > BIN_EMPTY_DISTANCE && *binFullNotified) {
        Serial.print("Bin ");
        Serial.print(binType);
        Serial.println(" EMPTY!");
        if (esp32Connected) {
            espLink.print("BIN_EMPTY:");
            espLink.println(binType == 'P' ? "PLASTIC" : (binType == 'G' ? "GLASS" : "CAN"));
        }
        *binFullNotified = false;
        // Buzzer will be deactivated in updateBuzzer() if all bins are empty
    }
}

// Function to move claim servo - Vending machine style 360 degree rotation
void moveClaimServo(int slotNum) {
    Serial.print("Vending machine rotation - Slot ");
    Serial.println(slotNum);
    
    Servo* servo = nullptr;
    int pin = 0;
    
    // Select the correct servo
    if (slotNum == 1) {
        servo = &claimServo1;
        pin = CLAIM_SERVO_PIN1;
    } else if (slotNum == 2) {
        servo = &claimServo2;
        pin = CLAIM_SERVO_PIN2;
    } else if (slotNum == 3) {
        servo = &claimServo3;
        pin = CLAIM_SERVO_PIN3;
    } else {
        Serial.println("Invalid slot number");
        return;
    }
    
    // Attach servo
    servo->attach(pin);
    delay(100);
    
    // Start at 0 degrees
    servo->write(0);
    delay(200);
    
    // Vending machine rotation: Smooth 360-degree clockwise rotation
    // 0 -> 180 (first half) -> 0 (second half) = complete 360 rotation
    Serial.println("Starting rotation...");
    
    // First 180 degrees: 0 to 180 (clockwise)
    for (int angle = 0; angle <= 180; angle += 2) {
        servo->write(angle);
        delay(15); // Faster rotation for vending machine feel
    }
    
    // Brief pause at 180 (like vending machine mechanism)
    delay(100);
    
    // Second 180 degrees: 180 to 0 (completes the 360)
    for (int angle = 180; angle >= 0; angle -= 2) {
        servo->write(angle);
        delay(15); // Faster rotation
    }
    
    // Ensure we're back at 0
    servo->write(0);
    delay(200);
    
    // Detach to save power
    servo->detach();
    
    Serial.println("360-degree rotation complete!");
    
    // Notify ESP32 that rotation is complete
    if (esp32Connected) {
        espLink.print("CLAIM_SERVO_COMPLETE:");
        espLink.println(slotNum);
    }
}

// Function to handle ESP32 commands
void handleESP32Command(String command) {
    lastESP32Message = millis();
    esp32Connected = true;
    
    Serial.print("CMD: ");
    Serial.println(command);
    
    if (command == "INIT") {
        Serial.println("Arduino ready");
        espLink.println("ARDUINO_READY");
    }
    else if (command.startsWith("CLAIM_SERVO:")) {
        int slotNum = command.substring(12).toInt();
        moveClaimServo(slotNum);
    }
    else if (command == "PING") {
        espLink.println("PONG");
    }
    else if (command == "TEST") {
        espLink.println("TEST_RESPONSE");
    }
}

void setup() {
    Serial.begin(9600);
    delay(1000);
    
    Serial.println("Arduino starting...");
    
    espLink.begin(9600);
    delay(500);
    
    // Initialize ultrasonic sensor pins
    pinMode(TRIG_PIN_PLASTIC, OUTPUT);
    pinMode(ECHO_PIN_PLASTIC, INPUT);
    pinMode(TRIG_PIN_GLASS, OUTPUT);
    pinMode(ECHO_PIN_GLASS, INPUT);
    pinMode(TRIG_PIN_CAN, OUTPUT);
    pinMode(ECHO_PIN_CAN, INPUT);
    
    digitalWrite(TRIG_PIN_PLASTIC, LOW);
    digitalWrite(TRIG_PIN_GLASS, LOW);
    digitalWrite(TRIG_PIN_CAN, LOW);
    
    // Initialize buzzer pin
    pinMode(BUZZER_PIN, OUTPUT);
    digitalWrite(BUZZER_PIN, LOW);
    
    // Initialize servos
    claimServo1.attach(CLAIM_SERVO_PIN1);
    claimServo2.attach(CLAIM_SERVO_PIN2);
    claimServo3.attach(CLAIM_SERVO_PIN3);
    
    claimServo1.write(0);
    claimServo2.write(0);
    claimServo3.write(0);
    
    delay(500);
    
    claimServo1.detach();
    claimServo2.detach();
    claimServo3.detach();
    
    Serial.println("Servos ready");
    Serial.println("Waiting for ESP32...");
    
    delay(1000);
}

void loop() {
    unsigned long currentMillis = millis();
    
    // Check for ESP32 commands
    if (espLink.available()) {
        String command = espLink.readStringUntil('\n');
        command.trim();
        
        if (command.length() > 0 && command.length() < 50) {
            handleESP32Command(command);
        }
    }
    
    // Check ESP32 connection timeout
    if (esp32Connected && (currentMillis - lastESP32Message > esp32Timeout)) {
        esp32Connected = false;
        Serial.println("ESP32 timeout");
    }
    
    // Check bin status every 2 seconds
    if (currentMillis - lastCheck >= checkInterval) {
        lastCheck = currentMillis;
        
        checkBinStatus('P', TRIG_PIN_PLASTIC, ECHO_PIN_PLASTIC, &plasticBinFullNotified);
        delay(100);
        checkBinStatus('G', TRIG_PIN_GLASS, ECHO_PIN_GLASS, &glassBinFullNotified);
        delay(100);
        checkBinStatus('C', TRIG_PIN_CAN, ECHO_PIN_CAN, &canBinFullNotified);
        
        // Update buzzer status based on bin states
        updateBuzzer();
        
        if (esp32Connected) {
            Serial.println("ESP32 OK");
        } else {
            Serial.println("ESP32 OFF");
        }
    }
    
    // Update buzzer even between bin checks for continuous beeping
    updateBuzzer();
    
    delay(50);
}