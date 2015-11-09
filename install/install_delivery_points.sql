CREATE TABLE IF NOT EXISTS `PREFIX_dpd_delivery_points`(
    `id_dpd_delivery_points` int(11) NOT NULL AUTO_INCREMENT,
    `parcelshop_id` int(11) NOT NULL,
    `company` text NOT NULL,
    `city` text NOT NULL,
    `pcode` text NOT NULL,
    `street` text NOT NULL,
    `country` text NOT NULL,
    `email` text NOT NULL,
    `phone` text NOT NULL,
    `comment` text NOT NULL,
    `created_time` text NOT NULL,
    `update_time` text NOT NULL,
    `active` int(1) DEFAULT '1',
    `deleted` int(1) DEFAULT '0',
    PRIMARY KEY (`id_dpd_delivery_points`),
    UNIQUE KEY (`id_dpd_delivery_points`,`parcelshop_id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='DPD Delivery Points Data';