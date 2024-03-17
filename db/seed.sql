START TRANSACTION;
USE games_and_go;

-- Indirizzi
INSERT INTO Address (extension, house_number, street, city, postcode, country_code) VALUES
(NULL, 1, 'Via Roma', 'Roma', 00100, 'ITA'),
(NULL, 1, 'Via Milano', 'Milano', 20100, 'ITA'),
(NULL, 1, 'Via Napoli', 'Napoli', 80100, 'ITA');

-- Utenti
INSERT INTO `User` (email, password_hash, name, surname, birth_date, gender, phone_number) VALUES
('admin1@gamesandgo.com', SHA2('password1', 256), 'Mario', 'Rossi', '1980-01-01', 'male', 1234567890),
('customer1@gamesandgo.com', SHA2('password2', 256), 'Luigi', 'Bianchi', '1990-01-01', 'male', 2345678901),
('employee1@gamesandgo.com', SHA2('password3', 256), 'Anna', 'Verdi', '1985-01-01', 'female', 3456789012);

-- Admin
INSERT INTO Admin (user_id) VALUES (1);

-- Customer
INSERT INTO Customer (user_id, loyality_card_number, loyality_card_points) VALUES (2, 1001, 500);

-- Employee
INSERT INTO Employee (user_id, code, role, address_id) VALUES (3, 'EMP001', 'Sales', 1);

-- Prodotti
INSERT INTO Product (code, name, price, quantity) VALUES
('CON001', 'PlayStation 5', 299.99, 100),
('GAM001', 'The Last of Us Part II', 59.99, 100),
('ACC001', 'Dualshock', 19.99, 100),
('GUG001',  'The Last of Us Part II Strategy Guide', 14.99, 100);

-- Console
INSERT INTO Console (product_id, type) VALUES (1, 'Home console');

-- Game
INSERT INTO Game (product_id, plot, console_id) VALUES (2, 'A post-apocalyptic adventure game.', 1);

-- Accessory
INSERT INTO Accessory (product_id, type) VALUES (3, 'Controller');

-- GameGuide
INSERT INTO GameGuide (product_id) VALUES (4);

-- UserFeedback
INSERT INTO UserFeedback (customer_id, product_id, summary, description, rating) VALUES
(2, 1, 'Ottima console!', 'Sono molto soddisfatto della mia PlayStation 5. Le prestazioni sono incredibili e i giochi sembrano fantastici.', 5),
(2, 2, 'Storia coinvolgente', 'The Last of Us Part II ha una trama avvincente che mi ha tenuto incollato allo schermo.', 4),
(2, 3, 'Funzionale', 'Il controller funziona bene, ma avrei preferito se avesse più funzionalità.', 3),
(2, 4, 'Molto utile', 'La guida strategica di The Last of Us Part II mi ha aiutato a superare i livelli più difficili.', 4);

COMMIT;
