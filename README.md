# Community Calendar

### Background

This open source project was developed in Oberlin to create a central calendar for community events.

### Installation

The calendar can be run either through Docker (`./run.sh` will build the image and run it on port 8080) or by placing the files in your web servers document root. If you use Docker, create a file `includes/conn.php` that has the database connection information similiar to below:
```
<?php
$host = 'localhost';
$username = 'user';
$password = 'pass';
$con = "mysql:host={$host};dbname={$dbname};charset=utf8";

```
and the Dockerfile will handle the rest. If you're manually installing the calendar, you'll need to clone this repository, the [includes](https://github.com/EnvironmentalDashboard/includes) repository, and manually set up the connection file ([local](https://github.com/EnvironmentalDashboard/includes/blob/master/db.php#L5), [remote](https://github.com/EnvironmentalDashboard/includes/blob/master/db.php#L7)) referenced in `db.php` in the includes repo.

Additionally, with either approach, you'll need to create the database tables the schemas of which are dumped below:

```
CREATE TABLE `calendar` (
  `id` int(11) NOT NULL,
  `token` varchar(255) DEFAULT NULL,
  `event` varchar(255) NOT NULL,
  `start` int(10) NOT NULL,
  `end` int(10) NOT NULL,
  `description` varchar(500) NOT NULL,
  `extended_description` text NOT NULL,
  `extended_description_md` text,
  `event_type_id` int(11) NOT NULL DEFAULT '0',
  `loc_id` int(11) NOT NULL,
  `screen_ids` varchar(255) NOT NULL,
  `has_img` tinyint(1) NOT NULL DEFAULT '0',
  `approved` tinyint(1) DEFAULT NULL COMMENT 'NULL = not addressed yet, 0 = rejected, 1 = approved',
  `no_start_time` tinyint(1) NOT NULL DEFAULT '0',
  `no_end_time` tinyint(1) NOT NULL DEFAULT '0',
  `contact_email` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(15) DEFAULT NULL,
  `website` varchar(255) NOT NULL DEFAULT '',
  `repeat_end` int(10) UNSIGNED NOT NULL COMMENT 'Either a unix timestamp or number of times to repeat event',
  `repeat_on` varchar(255) DEFAULT NULL COMMENT 'Day index to repeat on',
  `sponsors` varchar(255) DEFAULT NULL,
  `room_num` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `calendar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`);

ALTER TABLE `calendar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;



CREATE TABLE `calendar_event_types` (
  `id` int(11) NOT NULL,
  `event_type` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `calendar_event_types`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `calendar_event_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;



CREATE TABLE `outbox` (
  `id` int(11) NOT NULL,
  `recipient` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL DEFAULT '',
  `unsub_header` varchar(255) NOT NULL DEFAULT '',
  `txt_message` text NOT NULL,
  `html_message` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Outbox for emails';

ALTER TABLE `outbox`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `outbox`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;



CREATE TABLE `newsletter_recipients` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


ALTER TABLE `newsletter_recipients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `newsletter_recipients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;





CREATE TABLE `newsletter_prefs` (
  `id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `event_type_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `newsletter_prefs`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `newsletter_prefs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;





CREATE TABLE `calendar_sponsors` (
  `id` int(11) NOT NULL,
  `sponsor` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `calendar_sponsors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sponsor` (`sponsor`);

ALTER TABLE `calendar_sponsors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;





CREATE TABLE `calendar_screens` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `calendar_screens`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `calendar_screens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;





CREATE TABLE `calendar_locs` (
  `id` int(11) NOT NULL,
  `location` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL DEFAULT '',
  `img` mediumblob
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `calendar_locs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `location` (`location`);

ALTER TABLE `calendar_locs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
```