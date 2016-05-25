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

CREATE TABLE authToken (
  id INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  token VARCHAR(60) NOT NULL UNIQUE,
  createdAt DATETIME,
  ip VARCHAR(20) DEFAULT NULL,
  userId INT(11) NOT NULL
) engine='InnoDB';

ALTER TABLE authToken ADD CONSTRAINT fk_user_id FOREIGN KEY (userId) REFERENCES user(id);
CREATE INDEX user_email_idx ON user(email);
CREATE INDEX authToken_token_idx ON authToken(token);