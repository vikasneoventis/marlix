<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to salesitoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact salesitoris.com for more information.
 *
 * category   ITORIS
 * package    ITORIS_M2_PRODUCT_TABS
 * copyright  Copyright (c) 2016 ITORIS INC. (http://www.itoris.com)
 * license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */


namespace Itoris\Producttabsslider\Block\Adminhtml\Grid\Render;


class ActionOrder extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{
    protected $increment=0;

    protected function _toLinkHtml($action, \Magento\Framework\DataObject $row)
    {


        $actionAttributes = new \Magento\Framework\DataObject();
        $actionCaption = '';
        $this->_transformActionData($action, $actionCaption, $row);

        if (isset($action['confirm'])) {
            $action['onclick'] = 'return window.confirm(\'' . addslashes(
                    $this->escapeHtml($action['confirm'])
                ) . '\')';
            unset($action['confirm']);
        }
        $order = $this->getColumn()->getData('order');

        $idConcat=$this->getColumn()->getData('id_concat');
        $product=$this->getColumn()->getData('id_product');
        $store=$this->getColumn()->getData('id_store');
        $count=count($order)-1;
        $actionAttributes->setData($action);
		
		

        if($store==NULL && $product==NULL) {
            if ($count != 0) {
                if($this->getRequest()->getParam('store')==NULL) {
                    if ($this->increment !== 0 && $count != $this->increment) {
                        $htmlOrder = '<a data_id_tab_down="' . @$idConcat[$this->increment + 1] . '" data_id_tab_this="' . @$idConcat[$this->increment] . '" class="sort-arrow-down" data_order_down="' . @$order[$this->increment + 1] . '" data_order_this="' . @$order[$this->increment] . '"></a>
                <a data_id_tab_up="' . @$idConcat[$this->increment - 1] . '" data_id_tab_this="' . @$idConcat[$this->increment] . '" class="sort-arrow-up" data_order_up="' . @$order[$this->increment - 1] . '" data_order_this="' . @$order[$this->increment] . '" ></a><a class="number_order" style=" text-decoration: none; color:#303030">' . ($this->increment + 1) . '</a>';
                        $this->increment++;
                        return $htmlOrder;
                    } elseif ($this->increment == 0) {
                        $htmlOrder = '<a data_id_tab_down="' . @$idConcat[$this->increment + 1] . '" data_id_tab_this="' . @$idConcat[$this->increment] . '" class="sort-arrow-down" data_order_down="' . @$order[$this->increment + 1] . '" data_order_this="' . @$order[$this->increment] . '"></a>
                 <a class="number_order" style=" text-decoration: none; color:#303030">' . ($this->increment + 1) . '</a>';
                        $this->increment++;
                        return $htmlOrder;
                    } else {
                        $htmlOrder = '<a data_id_tab_up="' . @$idConcat[$this->increment - 1] . '" data_id_tab_this="' . @$idConcat[$this->increment] . '" class="sort-arrow-up" data_order_up="' . @$order[$this->increment - 1] . '" data_order_this="' . @$order[$this->increment] . '">
                </a><a class="number_order" style=" text-decoration: none; color:#303030">' . ($this->increment + 1) . '</a>';
                        $this->increment++;
                        return $htmlOrder;
                    }
                }else{
                    @$valueId=$this->getColumn()->getData('id_value');
                    if ($this->increment !== 0 && $count != $this->increment) {
                        $htmlOrder = '<a data_id_value ="'. @$valueId[$this->increment].'" data_next_value ='. @$valueId[$this->increment+1].' data_product="'.$product.'" data_id_tab_down="' . @$idConcat[$this->increment + 1] . '" data_id_tab_this="' . @$idConcat[$this->increment] . '" class="sort-arrow-down" data_order_down="' . @$order[$this->increment + 1] . '" data_order_this="' . @$order[$this->increment] . '"></a><a data_prev_value="'. @$valueId[$this->increment-1].'" data_id_value ="'. @$valueId[$this->increment].'" data_product="'.$product.'" data_id_tab_up="' . @$idConcat[$this->increment - 1] .'" data_id_tab_this="' . @$idConcat[$this->increment] . '" class="sort-arrow-up" data_order_up="' . @$order[$this->increment - 1] . '" data_order_this="' . @$order[$this->increment] . '" ></a><a class="number_order" style=" text-decoration: none; color:#303030">' . ($this->increment+1) . '</a>';
                        $this->increment++;
                        return $htmlOrder;
                    } elseif ($this->increment == 0) {
                        $htmlOrder = '<a data_id_value ="'. @$valueId[$this->increment].'"  data_next_value ='. @$valueId[$this->increment+1].' data_product="'.$product.'" data_id_tab_down="' . @$idConcat[$this->increment + 1] . '" data_id_tab_this="' . @$idConcat[$this->increment] . '" class="sort-arrow-down" data_order_down="' . @$order[$this->increment + 1] . '" data_order_this="' . @$order[$this->increment] . '"></a>
                 <a class="number_order" style=" text-decoration: none; color:#303030">' . ($this->increment+1) . '</a>';
                        $this->increment++;
                        return $htmlOrder;
                    } else {
                        $htmlOrder = '<a data_prev_value="'. @$valueId[$this->increment-1].'" data_id_value ="'. @$valueId[$this->increment].'" data_product="'.$product.'" data_id_tab_up="' . @$idConcat[$this->increment - 1] . '" data_id_tab_this="' . @$idConcat[$this->increment] . '" class="sort-arrow-up" data_order_up="' . @$order[$this->increment - 1] . '" data_order_this="' . @$order[$this->increment] . '">
                </a><a class="number_order" style=" text-decoration: none; color:#303030">' . ($this->increment+1) . '</a>';
                        $this->increment++;
                        return $htmlOrder;
                    }
                }
            } else {
                return '<a class="number_order"  style="text-decoration:none; color:#303030" >' . ($this->increment+1) . '</a>';
            }
        }elseif($store!=NULL && $product==NULL){
            @$valueId=$this->getColumn()->getData('id_value');
            if ($count != 0) {
                if ($this->increment !== 0 && $count != $this->increment) {
                    $htmlOrder = '<a data_store="'.$store.'" data_id_value ="'. @$valueId[$this->increment].'" data_next_value ='. @$valueId[$this->increment+1].' data_id_tab_down="' . @$idConcat[$this->increment + 1] . '" data_id_tab_this="' . @$idConcat[$this->increment] . '" class="sort-arrow-down" data_order_down="' . @$order[$this->increment + 1] . '" data_order_this="' . @$order[$this->increment] . '"></a><a data_store="'.$store.'" data_prev_value="'. @$valueId[$this->increment-1].'" data_id_value ="'. @$valueId[$this->increment].'" data_id_tab_up="' . @$idConcat[$this->increment - 1] .'" data_id_tab_this="' . @$idConcat[$this->increment] . '" class="sort-arrow-up" data_order_up="' . @$order[$this->increment - 1] . '" data_order_this="' . @$order[$this->increment] . '" ></a><a class="number_order" style=" text-decoration: none; color:#303030">' . ($this->increment+1) . '</a>';
                    $this->increment++;
                    return $htmlOrder;
                } elseif ($this->increment == 0) {
                    $htmlOrder = '<a data_store="'.$store.'" data_id_value ="'. @$valueId[$this->increment].'"  data_next_value ='. @$valueId[$this->increment+1].' data_id_tab_down="' . @$idConcat[$this->increment + 1] . '" data_id_tab_this="' . @$idConcat[$this->increment] . '" class="sort-arrow-down" data_order_down="' . @$order[$this->increment + 1] . '" data_order_this="' . @$order[$this->increment] . '"></a>
                 <a class="number_order" style=" text-decoration: none; color:#303030">' . ($this->increment+1) . '</a>';
                    $this->increment++;
                    return $htmlOrder;
                } else {
                    $htmlOrder = '<a data_store="'.$store.'" data_prev_value="'. @$valueId[$this->increment-1].'" data_id_value ="'. @$valueId[$this->increment].'" data_id_tab_up="' . @$idConcat[$this->increment - 1] . '" data_id_tab_this="' . @$idConcat[$this->increment] . '" class="sort-arrow-up" data_order_up="' . @$order[$this->increment - 1] . '" data_order_this="' . @$order[$this->increment] . '">
                </a><a class="number_order" style=" text-decoration: none; color:#303030">' . ($this->increment+1) . '</a>';
                    $this->increment++;
                    return $htmlOrder;
                }
            } else {
                return '<a class="number_order" style=" text-decoration: none; color:#303030">' . ($this->increment+1) . '</a>';
            }
        }elseif($store==NULL && $product!=NULL){
            @$valueId=$this->getColumn()->getData('id_value');
            if ($count != 0) {

					if ($this->increment !== 0 && $count != $this->increment) {
                    $htmlOrder = '<a data_id_value ="'. @$valueId[$this->increment].'" data_next_value ='. @$valueId[$this->increment+1].' data_product="'.$product.'" data_id_tab_down="' . @$idConcat[$this->increment + 1] . '" data_id_tab_this="' . @$idConcat[$this->increment] . '" class="sort-arrow-down" data_order_down="' . @$order[$this->increment + 1] . '" data_order_this="' . @$order[$this->increment] . '"></a><a data_prev_value="'. @$valueId[$this->increment-1].'" data_id_value ="'. @$valueId[$this->increment].'" data_product="'.$product.'" data_id_tab_up="' . @$idConcat[$this->increment - 1] .'" data_id_tab_this="' . @$idConcat[$this->increment] . '" class="sort-arrow-up" data_order_up="' . @$order[$this->increment - 1] . '" data_order_this="' . @$order[$this->increment] . '" ></a><a class="number_order" style=" text-decoration: none; color:#303030">' . ($this->increment+1) . '</a>';
                    $this->increment++;
                    return $htmlOrder;
                } elseif ($this->increment == 0) {
                    $htmlOrder = '<a data_id_value ="'. @$valueId[$this->increment].'"  data_next_value ='. @$valueId[$this->increment+1].' data_product="'.$product.'" data_id_tab_down="' . @$idConcat[$this->increment + 1] . '" data_id_tab_this="' . @$idConcat[$this->increment] . '" class="sort-arrow-down" data_order_down="' . @$order[$this->increment + 1] . '" data_order_this="' . @$order[$this->increment] . '"></a>
                 <a class="number_order" style=" text-decoration: none; color:#303030">' . ($this->increment+1) . '</a>';
                    $this->increment++;
                    return $htmlOrder;
                } else {
                    $htmlOrder = '<a data_prev_value="'. @$valueId[$this->increment-1].'" data_id_value ="'. @$valueId[$this->increment].'" data_product="'.$product.'" data_id_tab_up="' . @$idConcat[$this->increment - 1] . '" data_id_tab_this="' . @$idConcat[$this->increment] . '" class="sort-arrow-up" data_order_up="' . @$order[$this->increment - 1] . '" data_order_this="' . @$order[$this->increment] . '">
                </a><a class="number_order" style=" text-decoration: none; color:#303030">' . ($this->increment+1) . '</a>';
                    $this->increment++;
                    return $htmlOrder;
                }

            } else {
                return '<a class="number_order" style=" text-decoration: none; color:#303030">' . ($this->increment+1) . '</a>';
            }
        }else{
            @$valueId=$this->getColumn()->getData('id_value');
            if ($count != 0) {

					if ($this->increment !== 0 && $count != $this->increment) {
                    $htmlOrder = '<a data_store="'.$store.'" data_id_value ="'. @$valueId[$this->increment].'" data_next_value ='. @$valueId[$this->increment+1].' data_product="'.$product.'" data_id_tab_down="' . @$idConcat[$this->increment + 1] . '" data_id_tab_this="' . @$idConcat[$this->increment] . '" class="sort-arrow-down" data_order_down="' . @$order[$this->increment + 1] . '" data_order_this="' . @$order[$this->increment] . '"></a><a data_store="'.$store.'" data_prev_value="'. @$valueId[$this->increment-1].'" data_id_value ="'. @$valueId[$this->increment].'" data_product="'.$product.'" data_id_tab_up="' . @$idConcat[$this->increment - 1] .'" data_id_tab_this="' . @$idConcat[$this->increment] . '" class="sort-arrow-up" data_order_up="' . @$order[$this->increment - 1] . '" data_order_this="' . @$order[$this->increment] . '" ></a><a class="number_order" style=" text-decoration: none; color:#303030">' . ($this->increment+1) . '</a>';
                    $this->increment++;
                    return $htmlOrder;
                } elseif ($this->increment == 0) {
                    $htmlOrder = '<a data_store="'.$store.'" data_id_value ="'. @$valueId[$this->increment].'"  data_next_value ='. @$valueId[$this->increment+1].' data_product="'.$product.'" data_id_tab_down="' . @$idConcat[$this->increment + 1] . '" data_id_tab_this="' . @$idConcat[$this->increment] . '" class="sort-arrow-down" data_order_down="' . @$order[$this->increment + 1] . '" data_order_this="' . @$order[$this->increment] . '"></a>
                 <a class="number_order" style=" text-decoration: none; color:#303030">' . ($this->increment+1) . '</a>';
                    $this->increment++;
                    return $htmlOrder;
                } else {
                    $htmlOrder = '<a data_store="'.$store.'" data_prev_value="'. @$valueId[$this->increment-1].'" data_id_value ="'. @$valueId[$this->increment].'" data_product="'.$product.'" data_id_tab_up="' . @$idConcat[$this->increment - 1] . '" data_id_tab_this="' . @$idConcat[$this->increment] . '" class="sort-arrow-up" data_order_up="' . @$order[$this->increment - 1] . '" data_order_this="' . @$order[$this->increment] . '">
                </a><a class="number_order" style=" text-decoration: none; color:#303030">' . ($this->increment+1) . '</a>';
                    $this->increment++;
                    return $htmlOrder;
                }

	
            } else {
                return '<a class="number_order" style=" text-decoration: none; color:#303030">' . ($this->increment+1) . '</a>';
            }
        }
    }
}