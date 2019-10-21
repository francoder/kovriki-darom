<?php

class nc_payment_admin_ui extends ui_config {

    /**
     * @param $tree_node
     */
    public function __construct($tree_node) {
        $this->headerText = NETCAT_MODULE_PAYMENT_NAME;
        $this->locationHash = "module.payment.$tree_node";
        $this->treeMode = "modules";
        $this->treeSelectedNode = "payment-$tree_node";
    }

    /**
     * @param string $caption
     */
    public function add_submit_button($caption = NETCAT_MODULE_PAYMENT_ADMIN_BUTTON_SAVE) {
        $this->actionButtons[] = array(
            'id' => 'submit_form',
            'caption' => $caption,
            'action' => 'mainView.submitIframeForm()'
        );
    }

    /**
     *
     *
     * @param null $back_button_href
     * @param string $cancel_button_caption
     */
    public function add_cancel_button($back_button_href = null, $cancel_button_caption = NETCAT_MODULE_PAYMENT_ADMIN_BUTTON_BACK) {
        $back_button = array(
            'id' => 'go_back',
            'caption' => $cancel_button_caption,
            'align' => 'left'
        );
        if ($back_button_href) {
            $back_button['location'] = $back_button_href;
        } else {
            $back_button['action'] = 'history.back(1)';
        }
        $this->actionButtons[] = $back_button;
    }

    /**
     * @param $hash
     */
    public function set_location_hash($hash) {
        $this->locationHash = "module.payment.$hash";
    }

}