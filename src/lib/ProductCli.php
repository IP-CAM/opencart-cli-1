<?php

/**
 * Class ProductCli
 *
 * @author Istvan Dobrentei
 * @link https://www.dobrenteiistvan.hu
 */
class ProductCli extends BaseCommandCli
{
    protected $_init;
    protected $_value;
    protected $_product_id;
    protected $_sku;

    /**
     * ProductCli constructor.
     *
     * @param $initClass BaseCli
     * @param $action
     * @param $params
     */
    public function __construct($initClass, $action, $params)
    {
        $this->_init = $initClass;
        $this->_init->load->model('catalog/product');
        $this->_value = isset($params['value']) ? $params['value'] : 'NA';
        $this->_product_id = isset($params['id']) ? (int)$params['id'] : 'NA';
        $this->_sku = isset($params['sku']) ? $params['sku'] : 'NA';
        $method = 'set' . ucfirst($action);
        if($this->_sku && $this->_sku != 'NA')
        {
            $this->_product_id = $this->getProductId($this->_sku);
        }
        $this->$method();
    }

    protected function setPrice()
    {
        if($this->_product_id && $this->_product_id != 'NA')
        {
            if($this->_value != 'NA')
            {
                $this->_init->db->query("UPDATE " . DB_PREFIX . "product SET price='" .
                    $this->_init->db->escape($this->_value) . "' WHERE product_id='" . (int)$this->_product_id . "'");

                if($this->_init->db->countAffected())
                {
                    $this->result(self::SUCCESS_RESULT, array());
                }
            }
        }
    }

    protected function setQuantity()
    {
        if($this->_product_id && $this->_product_id != 'NA')
        {
            if($this->_value != 'NA')
            {
                $this->_init->db->query("UPDATE " . DB_PREFIX . "product SET quantity='" .
                    $this->_init->db->escape($this->_value) . "' WHERE product_id='" . (int)$this->_product_id . "'");

                if($this->_init->db->countAffected())
                {
                    $this->result(self::SUCCESS_RESULT, array());
                }
            }
        }
    }

    protected function getProductId($sku)
    {
        $query = $this->_init->db->query("SELECT product_id FROM " . DB_PREFIX . "product 
        WHERE sku = '" . $this->_init->db->escape($sku) . "'");

        return isset($query->row['product_id']) ? (int)$query->row['product_id'] : 0;
    }
}