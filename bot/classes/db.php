<?php

class ConnectDatabase
{
    private $location;
    private $db_name;
    private $db_username;
    private $db_password;

    public function __construct($location, $db_name, $db_username, $db_password)
    {
        $this->location = $location;
        $this->db_name = $db_name;
        $this->db_username = $db_username;
        $this->db_password = $db_password;
    }

    public function connect()
    {
        $sql = new mysqli($this->location, $this->db_username, $this->db_password, $this->db_name);
        if ($sql->connect_error) {
            return json_encode(['status' => false, 'message' => 'Connect Error (' . $sql->connect_errno . ') ' . $sql->connect_error], 448);
        } else {
            return json_encode(['status' => true, 'message' => 'Database is connected.'], 448);
        }
    }

    function getSql()
    {
        $sql = new mysqli($this->location, $this->db_username, $this->db_password, $this->db_name);
        return $sql;
    }

    function createTables()
    {
        mysqli_multi_query(self::getSql(), "CREATE TABLE IF NOT EXISTS `users` (
            `row` INT(11) AUTO_INCREMENT PRIMARY KEY,
            `from_id` BIGINT(20) NOT NULL,
            `step` VARCHAR(500) DEFAULT 'none',
            `coin` BIGINT(100) DEFAULT 0,
            `join_time` BIGINT DEFAULT NULL,
            `get_test_account` BOOLEAN DEFAULT 0,
            `phone` BIGINT DEFAULT NULL,
            `status` BOOLEAN DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;");

        mysqli_multi_query(self::getSql(), "CREATE TABLE IF NOT EXISTS `orders` (
            `row` INT(11) AUTO_INCREMENT PRIMARY KEY,
            `from_id` BIGINT(20) NOT NULL,
            `type` TEXT NOT NULL,
            `panel` INT NOT NULL,
            `inbound_id` INT DEFAULT NULL,
            `date` INT(11) NOT NULL,
            `volume` INT(11) NOT NULL,
            `ip_limit` INT(11) NOT NULL,
            `price` BIGINT(20) NOT NULL,
            `config_link` TEXT NOT NULL,
            `remark` VARCHAR(50) DEFAULT NULL,
            `discount` BIGINT DEFAULT NULL,
            `is_repre` BOOLEAN DEFAULT false,
            `is_buy_extra_ip` BOOLEAN DEFAULT false,
            `caption` TEXT NOT NULL,
            `buy_time` BIGINT(20) NOT NULL,
            `code` BIGINT(20) NOT NULL,
            `status` BOOLEAN DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;");

        mysqli_multi_query(self::getSql(), "CREATE TABLE IF NOT EXISTS `panels` (
            `row` INT(11) AUTO_INCREMENT PRIMARY KEY,
            `name` TEXT NOT NULL,
            `address` TEXT NOT NULL,
            `domain` VARCHAR(100) DEFAULT NULL,
            `username` TEXT NOT NULL,
            `password` TEXT NOT NULL,
            `session` TEXT NOT NULL,
            `inbound_id` INT DEFAULT NULL,
            `added_time` INT DEFAULT NULL,
            `buy_limit` BIGINT(20) DEFAULT NULL,
            `prefix` VARCHAR(50) DEFAULT 'S',
            `code` BIGINT(20) NOT NULL,
            `status` BOOLEAN DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;");

        mysqli_multi_query(self::getSql(), "CREATE TABLE IF NOT EXISTS `marzban_panels` (
            `row` INT(11) AUTO_INCREMENT PRIMARY KEY,
            `name` TEXT NOT NULL,
            `address` TEXT NOT NULL,
            `username` TEXT NOT NULL,
            `password` TEXT NOT NULL,
            `session` TEXT NOT NULL,
            `protocols` VARCHAR(500) NOT NULL,
            `added_time` INT DEFAULT NULL,
            `buy_limit` BIGINT(20) DEFAULT NULL,
            `prefix` VARCHAR(50) DEFAULT 'S',
            `code` BIGINT(20) NOT NULL,
            `status` BOOLEAN DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;");

        mysqli_multi_query(self::getSql(), "CREATE TABLE IF NOT EXISTS `marzban_inbounds` (
            `row` INT(11) AUTO_INCREMENT PRIMARY KEY,
            `panel` BIGINT NOT NULL,
            `name` TEXT NOT NULL,
            `protocol` TEXT NOT NULL,
            `status` BOOLEAN DEFAULT true
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;");

        mysqli_multi_query(self::getSql(), "CREATE TABLE IF NOT EXISTS `single_plan` (
            `row` int(200) AUTO_INCREMENT PRIMARY KEY,
            `name` varchar(100) NOT NULL,
            `panels` VARCHAR(100) DEFAULT NULL,
            `ip_limit_price` BIGINT DEFAULT 0,
            `is_repre` BOOLEAN DEFAULT 0,
            `status` BOOLEAN DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;");

        mysqli_multi_query(self::getSql(), "CREATE TABLE IF NOT EXISTS `multiple_plan` (
            `row` int(200) AUTO_INCREMENT PRIMARY KEY,
            `type` INT DEFAULT NULL,
            `name` varchar(100) NOT NULL,
            `limit` BIGINT(20) DEFAULT NULL,
            `date` BIGINT(20) DEFAULT NULL,
            `ip_limit` BIGINT(20) DEFAULT 1,
            `price` BIGINT(20) DEFAULT 0,
            `code` BIGINT(20) NOT NULL,
            `status` BOOLEAN DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;");

        mysqli_multi_query(self::getSql(), "CREATE TABLE IF NOT EXISTS `temporary_invoices` (
            `row` int(200) AUTO_INCREMENT PRIMARY KEY,
            `from_id` BIGINT(20) NOT NULL,
            `type` varchar(100) DEFAULT NULL,
            `panel` INT DEFAULT NULL,
            `limit` BIGINT(20) DEFAULT NULL,
            `date` BIGINT(20) DEFAULT NULL,
            `ip_limit` BIGINT(20) DEFAULT NULL,
            `count` BIGINT(20) DEFAULT NULL,
            `price` BIGINT(20) DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;");

        mysqli_multi_query(self::getSql(), "CREATE TABLE IF NOT EXISTS `gifts` (
            `row` int(200) AUTO_INCREMENT PRIMARY KEY,
            `gift` varchar(100) NOT NULL,
            `price` BIGINT NOT NULL,
            `count_use` BIGINT DEFAULT 1,
            `status` BOOLEAN DEFAULT true
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;");

        mysqli_multi_query(self::getSql(), "CREATE TABLE IF NOT EXISTS `use_gifts` (
            `row` int(200) AUTO_INCREMENT PRIMARY KEY,
            `from_id` BIGINT(20) NOT NULL,
            `gift` varchar(100) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;");

        mysqli_multi_query(self::getSql(), "CREATE TABLE IF NOT EXISTS `copens` (
            `row` int(200) AUTO_INCREMENT PRIMARY KEY,
            `copen` varchar(100) NOT NULL,
            `percent` INT NOT NULL,
            `count_use` BIGINT DEFAULT 1,
            `status` BOOLEAN DEFAULT true
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;");

        mysqli_multi_query(self::getSql(), "CREATE TABLE IF NOT EXISTS `payment_factors` (
            `row` int(200) AUTO_INCREMENT PRIMARY KEY,
            `from_id` BIGINT(20) NOT NULL,
            `price` BIGINT DEFAULT 0,
            `file_id` VARCHAR(200) DEFAULT NULL,
            `code` BIGINT DEFAULT 0,
            `status` BOOLEAN DEFAULT false
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;");

        mysqli_multi_query(self::getSql(), "CREATE TABLE IF NOT EXISTS `refrals` (
            `from_id` BIGINT(20) NOT NULL,
            `to_id` BIGINT(20) NOT NULL,
            `status` BOOLEAN DEFAULT false
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;");

        mysqli_multi_query(self::getSql(), "CREATE TABLE IF NOT EXISTS `sends` (
            `is_send` BOOLEAN DEFAULT 0,
            `type` VARCHAR(30) DEFAULT NULL,
            `is_forward` BOOLEAN DEFAULT 0,
            `from_forward` BIGINT(20) DEFAULT NULL,
            `text` TEXT DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;");

        mysqli_multi_query(self::getSql(), "CREATE TABLE IF NOT EXISTS `admins` (
            `from_id` BIGINT(20) NOT NULL,
            `is_accept_fish` BOOLEAN DEFAULT false,
            `status` BOOLEAN DEFAULT true
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;");

        mysqli_multi_query(self::getSql(), "CREATE TABLE IF NOT EXISTS `channels` (
            `link` TEXT NOT NULL,
            `status` BOOLEAN DEFAULT true
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;");

        mysqli_multi_query(self::getSql(), "CREATE TABLE IF NOT EXISTS `representations` (
            `row` int(200) AUTO_INCREMENT PRIMARY KEY,
            `from_id` BIGINT(20) NOT NULL,
            `nick_name` VARCHAR(100) DEFAULT NULL,
            `stage_type` INT DEFAULT NULL,
            `status` BOOLEAN DEFAULT true
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;");

        mysqli_multi_query(self::getSql(), "CREATE TABLE IF NOT EXISTS `settings` (
            `buy_config_status` BOOLEAN DEFAULT true,
            `deposit_status` BOOLEAN DEFAULT true,
            `refral_status` BOOLEAN DEFAULT true,
            `refral_gift` BIGINT DEFAULT 2000,
            `bot_status` BOOLEAN DEFAULT true,
            `representation_status` BOOLEAN DEFAULT false,
            `new_member_status` BOOLEAN DEFAULT true,
            `phone_status` BOOLEAN DEFAULT false,
            `phone_country` VARCHAR(50) DEFAULT 'all',
            `add_previous_status` BOOLEAN DEFAULT false,
            `basic_after_first_use` BOOLEAN DEFAULT false,
            `representation_after_first_use` BOOLEAN DEFAULT true,
            `main_prefix` VARCHAR(50) DEFAULT 'service',
            `buy_remark_num` BIGINT DEFAULT 700,
            `education_channel` VARCHAR(60) DEFAULT 'https://t.me/telegram',
            `support` VARCHAR(50) DEFAULT '@support'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;");


        mysqli_multi_query(self::getSql(), "CREATE TABLE IF NOT EXISTS `representation_settings` (
            `row` int(200) AUTO_INCREMENT PRIMARY KEY,
            `stage_type` VARCHAR(50) DEFAULT NULL,
            `discount_percent` INT DEFAULT 0,
            `max_negative` BIGINT DEFAULT 0,
            `plan` INT DEFAULT NULL,
            `status` BOOLEAN DEFAULT true
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;");

        mysqli_multi_query(self::getSql(), "CREATE TABLE IF NOT EXISTS `payment_settings` (
            `card_number` BIGINT DEFAULT 1111222233334444,
            `card_name` VARCHAR(50) DEFAULT 'تنظیم نشده',
            `nowpayment_apikey` VARCHAR(100) DEFAULT 'تنظیم نشده',
            `type` VARCHAR(50) DEFAULT 'Arz'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;");

        mysqli_multi_query(self::getSql(), "CREATE TABLE IF NOT EXISTS `test_account_settings` (
            `panel` INT DEFAULT NULL,
            `inbound_id` INT DEFAULT NULL,
            `prefix` VARCHAR(50) DEFAULT 'Test', # result --> main_prefix + '-' + prefix + buy_remark_num
            `limit` BIGINT DEFAULT 200, # Based on MB
            `date` INT DEFAULT 1, # Based on day
            `ip_limit` INT DEFAULT 1, 
            `status` BOOLEAN DEFAULT false
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;");


        return json_encode(['status' => true, 'message' => 'The database is connected and installed.'], 448);
    }
}
