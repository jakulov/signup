CREATE DATABASE signup;

use signup;

CREATE TABLE user (
  id INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  email VARCHAR(120) NOT NULL UNIQUE,
  name VARCHAR(120) NOT NULL,
  phone VARCHAR(120),
  about TINYTEXT,
  photo VARCHAR(120),
  password VARCHAR(80),
  resetToken VARCHAR(60)
) engine='InnoDB';