CREATE TABLE IF NOT EXISTS `PREFIX_dpd_carrier_options`(
    `id_dpd_carrier_options` int(11) NOT NULL AUTO_INCREMENT,
    `carrier_id` int(11) NOT NULL,
    `reference_id` text NOT NULL,
    `type` text NULL,
    PRIMARY KEY (`id_dpd_carrier_options`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='Carrier Options';