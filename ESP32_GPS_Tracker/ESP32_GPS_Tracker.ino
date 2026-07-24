#include <WiFi.h>
#include <HTTPClient.h>
#include <TinyGPSPlus.h>

TinyGPSPlus gps;

// GPS UART
HardwareSerial gpsSerial(2);

// WiFi
const char* ssid = "AndroidAP";
const char* password = "12345678";

// PHP API
const char* serverURL = "http://10.39.183.140/gps_tracker/api/save.php";

// Device ID
const char* deviceCode = "ESP32-001";

// Built-in LED
#define LED_PIN 2

unsigned long lastSend = 0;
const unsigned long sendInterval = 30000;

void setup()
{
    Serial.begin(115200);

    pinMode(LED_PIN, OUTPUT);

    digitalWrite(LED_PIN, LOW);

    gpsSerial.begin(9600, SERIAL_8N1, 16, 17);

    Serial.println();
    Serial.println("================================");
    Serial.println(" ESP32 GPS TRACKER ");
    Serial.println("================================");

    connectWiFi();
}

void connectWiFi()
{
    Serial.println("Connecting to WiFi...");

    WiFi.begin(ssid, password);

    while (WiFi.status() != WL_CONNECTED)
    {
        digitalWrite(LED_PIN, !digitalRead(LED_PIN));
        delay(500);
        Serial.print(".");
    }

    digitalWrite(LED_PIN, HIGH);

    Serial.println();
    Serial.println("WiFi Connected!");

    Serial.print("ESP32 IP Address: ");
    Serial.println(WiFi.localIP());
}

void readGPS()
{
    while (gpsSerial.available())
    {
        gps.encode(gpsSerial.read());
    }
}

void printGPSInfo()
{
    Serial.println("--------------------------------");

    if (gps.location.isValid())
    {
        Serial.println("GPS FIX");

        Serial.print("Latitude : ");
        Serial.println(gps.location.lat(), 6);

        Serial.print("Longitude: ");
        Serial.println(gps.location.lng(), 6);

        Serial.print("Satellites: ");
        Serial.println(gps.satellites.value());

        Serial.print("Speed (km/h): ");
        Serial.println(gps.speed.kmph());

        Serial.print("Altitude (m): ");
        Serial.println(gps.altitude.meters());

        Serial.print("HDOP: ");
        Serial.println(gps.hdop.hdop());
    }
    else
    {
        Serial.println("Waiting for GPS Fix...");
        Serial.print("Satellites: ");
        Serial.println(gps.satellites.value());
    }

    Serial.println("--------------------------------");
}

void sendGPS()
{
    if (WiFi.status() != WL_CONNECTED)
    {
        Serial.println("WiFi Disconnected!");
        connectWiFi();
        return;
    }

    if (!gps.location.isValid())
    {
        Serial.println("No GPS Fix. Data not sent.");
        return;
    }

    HTTPClient http;

    http.begin(serverURL);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    String postData = "";

    postData += "device_code=" + String(deviceCode);
    postData += "&latitude=" + String(gps.location.lat(), 6);
    postData += "&longitude=" + String(gps.location.lng(), 6);
    postData += "&altitude=" + String(gps.altitude.meters(), 2);
    postData += "&speed=" + String(gps.speed.kmph(), 2);
    postData += "&satellites=" + String(gps.satellites.value());
    postData += "&gps_status=";

    if (gps.location.isValid())
        postData += "GPS FIX";
    else
        postData += "NO FIX";

    Serial.println();
    Serial.println("================================");
    Serial.println("Sending GPS Data...");
    Serial.println(postData);

    int httpResponseCode = http.POST(postData);

    Serial.print("HTTP Response Code: ");
    Serial.println(httpResponseCode);

    if (httpResponseCode > 0)
    {
        String response = http.getString();

        Serial.println("Server Response:");
        Serial.println(response);
    }
    else
    {
        Serial.print("POST Failed. Error: ");
        Serial.println(http.errorToString(httpResponseCode));
    }

    http.end();

    Serial.println("================================");
}

void loop()
{
    readGPS();

    static unsigned long lastPrint = 0;

    if (millis() - lastPrint >= 1000)
    {
        lastPrint = millis();

        printGPSInfo();
    }

    if (millis() - lastSend >= sendInterval)
    {
        lastSend = millis();

        sendGPS();
    }
}