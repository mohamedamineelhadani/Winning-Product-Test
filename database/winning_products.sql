CREATE DATABASE winning_products;

USE winning_products;

create table
    users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL UNIQUE,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        code VARCHAR(255) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

CREATE TABLE
    products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_user INT,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        image VARCHAR(255),
        total_score INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_user) REFERENCES users (id) ON DELETE CASCADE
    );

CREATE TABLE
    product_scores (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT,
        characteristic VARCHAR(255),
        score INT,
        notes TEXT,
        FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
    );

CREATE TABLE
    suppliers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255),
        phone VARCHAR(20),
        url VARCHAR(255),
        rating INT DEFAULT 0,
        shipping_days DECIMAL(10, 2) NOT NULL,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
    );



CREATE TABLE
    profit_calculations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT,
        cost_price DECIMAL(10, 2) NOT NULL DEFAULT 0,
        shipping_cost DECIMAL(10, 2) NOT NULL DEFAULT 0,
        other_costs DECIMAL(10, 2) NOT NULL DEFAULT 0,
        ad_cost DECIMAL(10, 2) NOT NULL DEFAULT 0,
        selling_price DECIMAL(10, 2) NOT NULL DEFAULT 0,
        gross_profit DECIMAL(10, 2) NOT NULL DEFAULT 0,
        gross_margin_percent DECIMAL(6, 2) NULL,
        net_profit DECIMAL(10, 2) NOT NULL DEFAULT 0,
        roi_percent DECIMAL(6, 2) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
    );

CREATE TABLE
    product_links (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT,
        link VARCHAR(255),
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
    );

