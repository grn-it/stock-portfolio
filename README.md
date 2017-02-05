# Stock Portfolio
Приложение для управления портфелями акций

---

Описание
-------
С помощью данного приложения можно создавать и управлять портфелями акций.
По каждой акции можно получить актуальную информацию, такую как: цена, изменение, объем.
Для каждого портфеля можно построить график изменения стоимости за последние 2 года.

[Тестовое задание](https://gist.github.com/smirik/4d6f323e8c2eba9054da)

Затраченное время
-----------------
1. Регистрация / авторизация пользователей - **1 день**
2. Создание портфеля акций (5-6 акций достаточно) для пользователя: стандартный CRUD - **3 дня**
3. Данные должны скачиваться с Yahoo Finance - **4 часа**
4. Сделать вывод графика "стоимость портфеля от времени" за 2 последних года по выбранным в п.2 акциям - **1 день**

Установка
---------
```
git clone https://github.com/grn-it/stock-portfolio.git
```

```
composer install
```

```
php app/console doctrine:migrations:migrate
```
