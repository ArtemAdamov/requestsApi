# RequestsAPI

RequestsAPI - это веб-приложение, разработанное на основе Symfony и PostgreSQL для обработки и управления заявками.

## Требования

1. Docker и Docker Compose
2. PHP 8.1
3. Composer

## Установка и запуск

### Клонирование репозитория

Склонируйте репозиторий с помощью Git:

```bash
git clone https://github.com/yourusername/requestsAPI.git
```
# Перейдите в директорию проекта:
```bash
cd requestsAPI
```
# Настройка Docker
Приложение использует Docker для обеспечения удобства развертывания и обеспечения единообразной среды.

Убедитесь, что Docker и Docker Compose установлены на вашем компьютере. Затем выполните следующкоманду в терминале для запуска приложения:
```bash
make start
```
# Использование
RequestsAPI работает посредством API, предоставляя конечные точки для обработки заявок. Полная документация API доступна по адресу /api/doc.json в приложении.

# Регистрация
```bash
/register
"email" => "",
"password" => "password"
/login_check
"email" => "",
"password" => "password"
```
получить jwt токен, использовать как bearer token использовать его при запросах к /api
# Тестирование
Для запуска тестов используйте следующую команду:
```bash
docker-compose exec app php bin/phpunit
```

# TODO list
1) swagger не показывает json body
2) unit test только один
3) phpstan внедрить
