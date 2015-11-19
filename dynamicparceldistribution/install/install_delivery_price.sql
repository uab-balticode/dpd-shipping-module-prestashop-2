CREATE TABLE IF NOT EXISTS `PREFIX_dpd_delivery_price`(
    `id_dpd_delivery_price` int(11) NOT NULL AUTO_INCREMENT,
    `postcode` int(11) NOT NULL,
    `price` text NOT NULL,
    `free_from_price` text NULL,
    `carrier_id` text NOT NULL,
    `weight` text NULL,
    `height` text NULL,
    `width` text NULL,
    `depth` text NULL,
    `oversized_price` text NULL,
    `overweight_price` text NULL,
    `id_shop` int(11) NOT NULL,
    PRIMARY KEY (`id_dpd_delivery_price`),
    UNIQUE KEY (`id_dpd_delivery_price`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='DPD Delivery Price by Postcode';