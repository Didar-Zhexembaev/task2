# Task 2

Был реализован метод, принимающий на вход IP-адрес и возвращающий информацию о местоположении.
Для получения информации об IP адресе использовано один из публичных сервисов (работающий с SOAP):
[IP2Geo](http://ws.cdyne.com/ip2geo/ip2geo.asmx)

Были использованы пакеты composer
- [x] PHP 7.4
- [x] Composer
- [x] Docker
- [x] PHP cтандарты PSR-4, PSR-12

# Установка
## Команды для разворота окружения
```shell
docker-compose build
docker-compose up -d
docker-compose exec php_apache composer install
```
## Входные данные
Данные берутся от формы с методом POST `<form method="POST">`
[форма для проверки](http://localhost)

## Выходные данные
Вывод данных в формате [XML](http://localhost) `POST`

## Настройки
Файл настройки URL `config/url.php`
Файл настройки роутинга `config/routes.yml`

## Примеры
**Payload:**

```
ipAddress=87.250.250.242
```
**Response:**
```xml
<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><soap:Body><ResolveIPResponse xmlns="http://ws.cdyne.com/"><ResolveIPResult><Country>Russian Federation</Country><Organization /><Latitude>55.7386</Latitude><Longitude>37.6068</Longitude><AreaCode>0</AreaCode><TimeZone /><HasDaylightSavings>false</HasDaylightSavings><Certainty>90</Certainty><RegionName /><CountryCode>RU</CountryCode></ResolveIPResult></ResolveIPResponse></soap:Body></soap:Envelope>
```

**Payload:**
```
ipAddress=87.250.250.256
```
**Response:**
```xml
<?xml version="1.0"?>
<response><status>error</status><messages><property>[ipAddress]</property><value>87.250.250.256</value><message>This is not a valid IP address.</message></messages></response>
```

**Payload:**
```
ipAddress=ip address
```
**Response:**
```xml
<?xml version="1.0"?>
<response><status>error</status><messages><property>[ipAddress]</property><value>ip address</value><message>This is not a valid IP address.</message></messages></response>
```