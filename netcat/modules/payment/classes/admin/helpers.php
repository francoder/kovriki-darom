<?php

class nc_payment_admin_helpers {

    /**
     * @param $datetime
     * @return false|string
     */
    static public function format_time($datetime) {
        // вероятно, нужно будет добавить другой формат для English
        $timestamp = strtotime($datetime);
        if ($timestamp === false) {
            return '&nbsp;';
        }
        return date('d.m.Y, H:i', $timestamp);
    }

    /**
     * @param $number
     * @return string
     */
    static public function format_number($number) {
        // вероятно, нужно будет добавить другой формат для English
        $formatted_number = number_format($number, 2, ',', ' ');
        $class = "nc--nowrap";
        if ($number < 0) {
            $formatted_number = str_replace('-', '&minus;', $formatted_number);
            $class .= " nc-text-red";
        }
        return "<span class=\"$class\">$formatted_number</span>";
    }

    /**
     * @param $tax_rate
     * @return string
     */
    static public function format_tax_rate($tax_rate) {
        return strlen($tax_rate)
            ? $tax_rate . '%'
            : NETCAT_MODULE_PAYMENT_ADMIN_INVOICE_ITEM_VAT_NONE;
    }

}