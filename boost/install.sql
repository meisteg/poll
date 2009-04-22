-- $Id: install.sql,v 1.1 2007/02/20 05:09:35 blindman1344 Exp $

CREATE TABLE poll (
id INT NOT NULL,
key_id INT DEFAULT '0' NOT NULL,
title VARCHAR(100) NOT NULL,
question TEXT NULL,
active SMALLINT NOT NULL,
users_only SMALLINT NOT NULL,
allow_comments SMALLINT NOT NULL,
created INT NOT NULL,
PRIMARY KEY (id)
);

CREATE TABLE poll_pins (
poll_id INT DEFAULT '0' NOT NULL,
key_id INT DEFAULT '0' NOT NULL
);

CREATE TABLE poll_options (
id INT NOT NULL,
poll_id INT DEFAULT '0' NOT NULL,
name VARCHAR(100) NOT NULL default '',
votes INT NOT NULL,
PRIMARY KEY (id)
);

CREATE TABLE poll_voted_ips (
id INT NOT NULL,
poll_id INT DEFAULT '0' NOT NULL,
ip text NOT NULL,
PRIMARY KEY (id)
);
