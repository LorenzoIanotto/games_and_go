START TRANSACTION;

CREATE DATABASE games_and_go;
USE games_and_go;

CREATE TABLE Address (
  id INTEGER AUTO_INCREMENT,
  extension INTEGER DEFAULT NULL,
  house_number INTEGER NOT NULL,
  street VARCHAR(255) NOT NULL,
  city VARCHAR(255) NOT NULL,
  postcode INTEGER NOT NULL,
  -- ISO 3166-1 alpha-3
  country_code CHAR(3) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE (extension, house_number, street, city, postcode, country_code)
);

CREATE TABLE Product (
  id INTEGER AUTO_INCREMENT,
  code VARCHAR(255) UNIQUE NOT NULL,
  price DECIMAL NOT NULL,
  quantity INTEGER NOT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE Console (
  product_id INTEGER NOT NULL,
  name VARCHAR(255) NOT NULL,
  type VARCHAR(255) NOT NULL,
  PRIMARY KEY (product_id),
  FOREIGN KEY (product_id) REFERENCES Product(id)
);

CREATE TABLE Game (
  product_id INTEGER NOT NULL,
  title VARCHAR(255) NOT NULL,
  plot TEXT NOT NULL,
  console_id INTEGER NOT NULL,
  PRIMARY KEY (product_id),
  FOREIGN KEY (product_id) REFERENCES Product(id),
  FOREIGN KEY (console_id) REFERENCES Console(product_id)
);

CREATE TABLE Accessory (
  product_id INTEGER NOT NULL,
  type VARCHAR(255) NOT NULL,
  PRIMARY KEY (product_id),
  FOREIGN KEY (product_id) REFERENCES Product(id)
);

CREATE TABLE GameGuide (
  product_id INTEGER NOT NULL,
  title VARCHAR(255) NOT NULL,
  PRIMARY KEY (product_id),
  FOREIGN KEY (product_id) REFERENCES Product(id)
);

CREATE TABLE `User` (
  id INTEGER AUTO_INCREMENT,
  email VARCHAR(255) UNIQUE NOT NULL,
  -- sha256
  password_hash VARCHAR(64) NOT NULL,
  name VARCHAR(255) NOT NULL,
  surname VARCHAR(255) NOT NULL,
  birth_date DATE NOT NULL,
  gender ENUM("male", "female", "other") NOT NULL,
  phone_number INTEGER NOT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE Customer (
  user_id INTEGER NOT NULL,
  loyality_card_number INTEGER UNIQUE NULL,
  loyality_card_points INTEGER DEFAULT 0 NOT NULL,
  PRIMARY KEY (user_id),
  FOREIGN KEY (user_id) REFERENCES `User`(id)
);

CREATE TABLE Employee (
  user_id INTEGER NOT NULL,
  code VARCHAR(255) UNIQUE NOT NULL,
  role VARCHAR(255) NOT NULL,
  address_id INTEGER NOT NULL,
  PRIMARY KEY (user_id),
  FOREIGN KEY (user_id) REFERENCES `User`(id),
  FOREIGN KEY (address_id) REFERENCES Address(id)
);

CREATE TABLE Admin (
  user_id INTEGER NOT NULL,
  PRIMARY KEY (user_id),
  FOREIGN KEY (user_id) REFERENCES `User`(id)
);

CREATE TABLE CustomerOrder (
  id INTEGER AUTO_INCREMENT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  customer_id INTEGER NOT NULL,
  payment_method ENUM ("bancomat", "credit_card", "cash_on_delivery", "bank_transfer") NOT NULL,
  payment_method_code VARCHAR(255) NOT NULL,
  payment_successful BOOLEAN DEFAULT false NOT NULL,
  total_amount DECIMAL(10,2) UNSIGNED NOT NULL,
  order_status ENUM ("packaging_in_progress", "delivery_in_progress", "delivered") NOT NULL,
  address_id INTEGER NOT NULL,
  PRIMARY KEY (id),
  UNIQUE (customer_id, created_at),
  FOREIGN KEY (customer_id) REFERENCES Customer(user_id),
  FOREIGN KEY (address_id) REFERENCES Address(id)
);

CREATE TABLE CustomerOrderProduct (
  order_id INTEGER NOT NULL,
  product_id INTEGER NOT NULL,
  quantity INTEGER NOT NULL,
  PRIMARY KEY (order_id, product_id),
  FOREIGN KEY (order_id) REFERENCES CustomerOrder(id),
  FOREIGN KEY (product_id) REFERENCES Product(id)
);

CREATE TABLE Vendor (
  id INTEGER AUTO_INCREMENT,
  vat_number VARCHAR(64) UNIQUE NOT NULL,
  business_name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  address_id INTEGER NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (address_id) REFERENCES Address(id)
);

CREATE TABLE CustomerAddress (
  customer_id INTEGER NOT NULL,
  address_id INTEGER NOT NULL,
  PRIMARY KEY (customer_id, address_id),
  FOREIGN KEY (customer_id) REFERENCES Customer(user_id),
  FOREIGN KEY (address_id) REFERENCES Address(id)
);

CREATE TABLE InternalOrder (
  id INTEGER AUTO_INCREMENT,
  employee_id INTEGER NOT NULL,
  vendor_id INTEGER NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  total_amount DECIMAL(10,2) UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  UNIQUE (employee_id, created_at),
  FOREIGN KEY (employee_id) REFERENCES Employee(user_id),
  FOREIGN KEY (vendor_id) REFERENCES Vendor(id)
);

CREATE TABLE InternalOrderProduct (
  order_id INTEGER NOT NULL,
  product_id INTEGER NOT NULL,
  quantity INTEGER NOT NULL,
  PRIMARY KEY (order_id, product_id),
  FOREIGN KEY (order_id) REFERENCES InternalOrder(id),
  FOREIGN KEY (product_id) REFERENCES Product(id)
);

COMMIT;
