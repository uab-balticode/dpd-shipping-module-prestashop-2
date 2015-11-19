CREATE TABLE IF NOT EXISTS `PREFIX_dpd_delivery_options`(
    `id_dpd_delivery_options` int(11) NOT NULL AUTO_INCREMENT,
    `cart_id` int (11) NOT NULL,
    `order_id` int(11) NOT NULL,
    `carrier_id` text NOT NULL,
    `address_id` text NOT NULL,
    `delivery_option` text NOT NULL,
    `lock` INT(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_dpd_delivery_options`),
    UNIQUE KEY (`id_dpd_delivery_options`,`order_id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='Order Delivery Options';