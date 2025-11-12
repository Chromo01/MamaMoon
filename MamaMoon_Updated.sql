-- Updated MamaMoon database with image URLs
CREATE DATABASE IF NOT EXISTS MamaMoon;
USE MamaMoon;

DROP TABLE IF EXISTS candles;
DROP TABLE IF EXISTS bracelets;
DROP TABLE IF EXISTS necklaces;
DROP TABLE IF EXISTS other_items;

CREATE TABLE candles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    scent_group VARCHAR(255) NOT NULL
);

CREATE TABLE bracelets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(255) NOT NULL
);

CREATE TABLE necklaces (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(255) NOT NULL
);

CREATE TABLE other_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(255) NOT NULL
);

INSERT INTO candles (name, description, price, image_url, scent_group)
VALUES
('Vanilla', 'Smooth vanilla scent', 10.00, 'images/vanilla.jpg', 'Sweet'),
('Lavender', 'Relaxing lavender', 10.00, 'images/lavender.jpg', 'Floral'),
('Rose', 'Floral and sweet', 10.00, 'images/rose.jpg', 'Floral'),
('Sandalwood', 'Earthy and grounding', 10.00, 'images/sandalwood.jpg', 'Spicy');

CREATE OR REPLACE VIEW all_products AS
SELECT 
  id,
  name,
  price,
  image_url,
  scent_group AS product_group,
  'candles' AS category
FROM candles

UNION ALL

SELECT 
  id,
  name,
  price,
  image_url,
  type_group AS product_group,
  'bracelets' AS category
FROM bracelets

UNION ALL

SELECT 
  id,
  name,
  price,
  image_url,
  type_group AS product_group,
  'necklaces' AS category
FROM necklaces

UNION ALL

SELECT 
  id,
  name,
  price,
  image_url,
  type_group AS product_group,
  'other_items' AS category
FROM other_items;
