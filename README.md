# Stock Portfolio

Приложение для управления портфелями акций

Описание
-------
С помощью данного приложения можно создавать и управлять портфелями акций.
По каждой акции можно получить актуальную информацию, такую как: цена, изменение, объем.
Для каждого портфеля можно построить график изменения стоимости за последние 2 года.

Установка
---------
```
git clone https://github.com/grn-it/stock-portfolio.git
```
Заходим в директорию **stock-portfolio** и выполняем:
```
composer install
```
```
php app/console doctrine:database:create
```
```
php app/console doctrine:migrations:migrate
```
Должен быть запущен **Redis** по стандартному адресу (127.0.0.1:6379)

Презентация
-----------
![](https://github.com/grn-it/stock-portfolio/blob/master/demo.gif)
