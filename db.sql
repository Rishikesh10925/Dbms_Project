-- REMA: Real Estate Management Application
-- Database schema (MySQL 8 / MariaDB / TiDB).
-- Columns mirror exactly what the PHP pages read and write.

CREATE TABLE IF NOT EXISTS users (
    user_id       INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50)  NOT NULL UNIQUE,
    password      VARCHAR(255) NOT NULL,              -- password_hash() output
    email         VARCHAR(255) NOT NULL,
    contact_info  VARCHAR(255) DEFAULT NULL,
    created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS properties (
    property_id               INT AUTO_INCREMENT PRIMARY KEY,
    user_id                   INT NOT NULL,           -- current owner / lister
    property_type             VARCHAR(30)  NOT NULL,  -- plot, flat, villa, farm_land, farm_house, barren_land
    property_size             DECIMAL(12,2) NOT NULL,
    size_unit                 VARCHAR(10)  NOT NULL,  -- sqm, sqyd
    ownership_status          VARCHAR(30)  NOT NULL,  -- sole, co_owned, mortgaged, leased, pending_transfer, disputed
    features                  TEXT,
    description               TEXT,
    estimated_price_per_unit  DECIMAL(14,2) NOT NULL,
    total_value               DECIMAL(16,2) NOT NULL,
    future_value              DECIMAL(16,2) DEFAULT NULL,
    seller_type               VARCHAR(20)  NOT NULL,  -- owner, third_party
    negotiation               TINYINT(1)   NOT NULL DEFAULT 0,
    brokering                 TINYINT(1)   NOT NULL DEFAULT 0,
    location                  VARCHAR(255) NOT NULL,
    city                      VARCHAR(100) NOT NULL,
    usage_type                VARCHAR(20)  NOT NULL,  -- sale, rent, lease, development
    status                    VARCHAR(20)  NOT NULL DEFAULT 'available',  -- available, pending, sold
    created_at                TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_properties_city_status (city, status),
    INDEX idx_properties_user (user_id),
    CONSTRAINT fk_properties_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS transactions (
    transaction_id    INT AUTO_INCREMENT PRIMARY KEY,
    property_id       INT NOT NULL,
    buyer_id          INT DEFAULT NULL,               -- set for buyer requests
    agent_id          INT DEFAULT NULL,               -- set for agent buy / sell
    transaction_type  VARCHAR(20) NOT NULL,           -- the property's usage_type, or 'sale'
    status            VARCHAR(20) NOT NULL DEFAULT 'pending',  -- pending, confirmed
    created_at        TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_transactions_buyer (buyer_id),
    INDEX idx_transactions_agent (agent_id),
    CONSTRAINT fk_transactions_property FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE,
    CONSTRAINT fk_transactions_buyer    FOREIGN KEY (buyer_id)    REFERENCES users(user_id)          ON DELETE SET NULL,
    CONSTRAINT fk_transactions_agent    FOREIGN KEY (agent_id)    REFERENCES users(user_id)          ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
