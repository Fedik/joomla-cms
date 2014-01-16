
--
-- Add the params column to
--

ALTER TABLE `#__content_types` ADD `params` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;

--
-- Dumping data for table `#__ucm_fields`
--

CREATE TABLE IF NOT EXISTS `#__ucm_fields` (
  `field_id` int(11) NOT NULL AUTO_INCREMENT,
  `field_name` varchar(255) NOT NULL,
  `field_type` varchar(255) NOT NULL,
  `type_id` int(11) NOT NULL,
  `locked` tinyint(2) NOT NULL DEFAULT '0' COMMENT 'Whether deletion allowed',
  PRIMARY KEY (`field_id`),
  KEY `field_id` (`field_id`),
  KEY `type_id` (`type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `#__ucm_layouts`
--

CREATE TABLE IF NOT EXISTS `#__ucm_layouts` (
  `layout_id` int(11) NOT NULL AUTO_INCREMENT,
  `layout_name` varchar(255) NOT NULL,
  `layout_title` varchar(255) NOT NULL,
  `type_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Content ID which Layout related',
  `params` text NOT NULL,
  PRIMARY KEY (`layout_id`),
  KEY `layout_name` (`layout_name`),
  KEY `type_id` (`type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `#__ucm_fields_layouts`
--

CREATE TABLE IF NOT EXISTS `#__ucm_fields_layouts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field_id` int(11) NOT NULL COMMENT 'ID of the related Field',
  `layout_id` int(11) NOT NULL COMMENT 'ID of the related Layout',
  `params` text NOT NULL COMMENT 'Field params for current layout',
  `ordering` int(11) NOT NULL DEFAULT '0' COMMENT 'Field position in current layout',
  `access` int(11) NOT NULL DEFAULT '1',
  `state` tinyint(3) NOT NULL DEFAULT '1',
  `stage` int(11) NOT NULL DEFAULT '0' COMMENT 'For multisteps layouts',
  PRIMARY KEY (`id`),
  KEY `field_id` (`field_id`),
  KEY `layout_id` (`layout_id`),
  KEY `access` (`access`),
  KEY `state` (`state`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

