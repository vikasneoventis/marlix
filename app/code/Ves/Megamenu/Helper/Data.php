<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_Megamenu
 * @copyright  Copyright (c) 2017 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */

namespace Ves\Megamenu\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	/**
	 * @var array
	 */
	static $arr = [];

	/**
	 * @var array
	 */
	static $categories = [];

	/**
	 * @var \Magento\Framework\Registry
	 */
	protected $_coreRegistry;

	/**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
	protected $_filterProvider;

	/**
     * @var \Magento\Cms\Model\Template\Filter
     */
	protected $_filter;

	/**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
	protected $_categoryFactory;


	protected $menu;

	/**
     * @var \Magento\Framework\Escaper
     */
	protected $_escaper;

	protected $_cats;

	/**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
	protected $_storeManager;

	protected $_url;

	/**
     * Group Collection
     */
	protected $_groupCollection;

	protected $_currentStore;

	protected $mediaUrl;

	protected $_catsCollection;

	protected $menuCategories;

	/**
	 * @param \Magento\Framework\App\Helper\Context      $context         
	 * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider  
	 * @param \Magento\Cms\Model\Template\Filter         $filter          
	 * @param \Magento\Framework\Registry                $registry        
	 * @param \Magento\Framework\Escaper                 $escaper         
	 * @param \Magento\Store\Model\StoreManagerInterface $storeManager    
	 * @param \Magento\Catalog\Model\CategoryFactory     $categoryFactory 
	 * @param \Magento\Customer\Model\Group              $groupManager    
	 * @param \Magento\Framework\Url                     $url             
	 */
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Cms\Model\Template\FilterProvider $filterProvider,
		\Magento\Cms\Model\Template\Filter $filter,
		\Magento\Framework\Registry $registry,
		\Magento\Framework\Escaper $escaper,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Catalog\Model\CategoryFactory $categoryFactory,
		\Magento\Customer\Model\Group $groupManager,
		\Magento\Framework\Url $url,
		\Ves\Megamenu\Model\Config\Source\StoreCategories $storeCategories
		) {
		parent::__construct($context);
		$this->_filterProvider  = $filterProvider;
		$this->_filter          = $filter;
		$this->_coreRegistry    = $registry;
		$this->_categoryFactory = $categoryFactory;
		$this->_escaper         = $escaper;
		$this->_storeManager    = $storeManager;
		$this->_url             = $url;
		$this->_groupCollection = $groupManager;
		$this->mediaUrl         = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
		$this->storeCategories  = $storeCategories;
	}

	public function setStore($store) {
		$this->_currentStore = $store;
		return $this;
	}

	public function getStore() {
		return $this->_currentStore;
	}

	public function filter($str, $storeId = '')
	{
		$filter = $this->_filterProvider->getPageFilter();
		$html   = $filter->filter($str);
		return $html;
	}

	public function subString($text, $length, $replacer = '...', $is_striped = true) {
		if ((int)$length==0) return $text;
		$text = ($is_striped == true) ? strip_tags($text) : $text;
		if (strlen($text) <= $length) {
			return $text;
		}
		$text = substr($text, 0, $length);
		$pos_space = strrpos($text, ' ');
		return substr($text, 0, $pos_space) . $replacer;
	}

	public function getCustomerGroups()
	{
		$data_array = [];

		$customer_groups = $this->_groupCollection->getCollection();

		foreach ($customer_groups as $item_group) {
			$data_array[] =  array('value' => $item_group->getId(), 'label' => $item_group->getCode());
		}

		return $data_array;

	}

	public function decodeWidgets($str) {
		$result = '';
		$imgs = [];
		$firstPosition = 0;
		$i = 0;
		$count = substr_count($str, 'title="{{widget');
		for ($i=0; $i < $count; $i++) {
			if ($firstPosition==0) $tmp = $firstPosition;
			$firstPosition = strpos($str, "<img", $tmp);
			$nextPosition = strpos($str, "/>", $firstPosition);
			$tmp = $firstPosition;
			$length = $nextPosition - $firstPosition;
			$img = substr($str, $firstPosition, $length+2);
			if (strpos($img, '{{widget')) {
				$f = strpos($img, "{{widget", 0);
				$n = strpos($img, '"', $f);
				$widgetCode = substr($img, $f, ($n-$f));
				$widgetHtml = $this->filter(html_entity_decode($widgetCode));
				if ($i==0) $result = $str;
				$result = str_replace($img, $widgetHtml, $result);
				$str = str_replace($img, '', $str);
			}
		}

		$count = substr_count($str, 'title="{widget');
		for ($i=0; $i < $count; $i++) {
			if ($firstPosition==0) $tmp = $firstPosition;
			$firstPosition = strpos($str, "<img", $tmp);
			$nextPosition = strpos($str, "/>", $firstPosition);
			$tmp = $firstPosition;
			$length = $nextPosition - $firstPosition;
			$img = substr($str, $firstPosition, $length+2);
			if (strpos($img, '{widget')) {
				$f = strpos($img, "{widget", 0);
				$n = strpos($img, '"', $f);
				$widgetCode = '{' . substr($img, $f, ($n-$f)) . '}';
				$widgetHtml = $this->filter(html_entity_decode($widgetCode));
				if ($i==0) $result = $str;
				$result = str_replace($img, $widgetHtml, $result);
				$str = str_replace($img, '', $str);
			}
		}

		$widgets = ['Magento_Widget/placeholder.gif', 'Magento_Catalog/images/product_widget_new.png', 'Magento_CatalogWidget/images/products_list.png', 'Magento/backend/en_US/Magento_Reports/images/product_widget_viewed.gif'];

		for ($z=0; $z < count($widgets); $z++) {
			$count = substr_count($str, $widgets[$z]);
			for ($i=0; $i < $count; $i++) {
				if ($firstPosition==0) $tmp = $firstPosition;
				$firstPosition = strpos($str, "<img", $tmp);
				$nextPosition = strpos($str, "/>", $firstPosition);
				$tmp = $firstPosition;
				$length = $nextPosition - $firstPosition;
				$img = substr($str, $firstPosition, $length+2);
				if (strpos($img, 'id="')) {
					$f = strpos($img, 'id="', 0);
					$n = strpos($img, '"', $f+4);
					$widgetCode = substr($img, $f+4, ($n-$f-4));
					$widgetCode = str_replace("--", "", $widgetCode);
					$widgetCode = base64_decode($widgetCode);
					$widgetHtml = $widgetCode;
					if ($i==0) $result = $str;
					$result = str_replace($img, $widgetHtml, $result);
					$str .= str_replace($img, '', $str);
				}
			}	
		}

		if ($result!='') {
			return $result;
		}
		return $str;
	}

	/**
     * Return brand config value by key and store
     *
     * @param string $key
     * @param \Magento\Store\Model\Store|int|string $store
     * @return string|null
     */
	public function getConfig($key, $store = null)
	{
		$store = $this->_storeManager->getStore($store);
		$websiteId = $store->getWebsiteId();

		$result = $this->scopeConfig->getValue(
			'vesmegamenu/' . $key,
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE,
			$store);
		return $result;
	}

	public function getCoreRegistry()
	{
		return $this->_coreRegistry;
	}

	public function renderMenuItemData($data = [] , $itemBuild = [], $menuItems)
	{
		$data_id = isset($data['id'])?$data['id']:0;
		$itemBuild = isset($menuItems[$data_id])?$menuItems[$data_id]:[];
		$children = [];
		if (isset($data['children']) && count($data['children']>0)) {
			foreach ($data['children'] as $k => $v) {
				$children[] = $this->renderMenuItemData($v, $itemBuild, $menuItems);
			}
		}
		$itemBuild['children'] = $children;
		return $itemBuild;
	}

	public function getMenu()
	{
		return $this->menu;
	}

	public function drawAnchor($item)
	{
		$hasChildren = false;
		$tChildren = false;
		if ($item['content_type'] == 'parentcat') {
			$catChildren = $this->getTreeCategories($item['parentcat']);
			if ($catChildren) $tChildren = true;
		}
		if (($item['show_footer'] && $item['footer_html']!='') || ($item['show_header'] && $item['header_html']!='') ||  ($item['show_left_sidebar'] && $item['left_sidebar_html']!='') || ($item['show_right_sidebar'] && $item['right_sidebar_html']!='') || ($item['show_content'] && ((($item['content_type'] == 'childmenu' || $item['content_type'] == 'dynamic') && (isset($item['children']) && count($item['children'])>0)) || ($item['content_type'] == 'content' && $item['content_html']!=''))) || ($item['content_type'] == 'parentcat' && $tChildren) ) $hasChildren = true;

		$html = $class = $style = $attr = '';

		// Design
		if (isset($item['color']) && $item['color']!='') {
			$style .= 'color: ' . $item['color'] . ';';
		}
		if (isset($item['bg_color']) && $item['bg_color']!='') {
			$style .= 'background-color: ' . $item['bg_color'] . ';';
		}
		if (isset($item['inline_css']) && $item['inline_css']!='') {
			$style .= $item['inline_css'];
		}
		if ($style!='') $style = 'style="' . $style . '"';

		$class .= ' nav-anchor';

		if ($item['content_type'] == 'dynamic') $class .= ' subdynamic';
		if ($item['is_group']) $class .= ' subitems-group';

		// Custom Link, Category Link
		$href = '';
		if ($item['link_type'] == 'custom_link') {
			$href = $this->filter($item['link']);
			if ($this->endsWith($href, '/')) {
				$href = substr_replace($href, "", -1);
			}
		} else if ($item['link_type'] == 'category_link') {
			if ($category = $this->getCategory($item['category'])) {
				$href = $category['url'];
				if($urls = parse_url($href)){
					$url_host = isset($urls['host'])?$urls['host']:"";
					$base_url = $this->_storeManager->getStore()->getBaseUrl();
					if($url_host && ($base_urls = parse_url($base_url))) {
						$base_urls['host'] = isset($base_urls['host'])?$base_urls['host']:"";
						if($url_host != $base_urls['host']){
							$href = str_replace($url_host, $base_urls['host'], $href);
						}
					}
				}
			}
		}

		if ($class!='') $class = 'class="' . $class . '"';

		// Attributes
		if (isset($item['hover_color']) && $item['hover_color']) {
			$attr .= ' data-hover-color="' . $item['hover_color'] . '"';
		}
		if (isset($item['bg_hover_color']) && $item['bg_hover_color']) {
			$attr .= ' data-hover-bgcolor="' . $item['bg_hover_color'] . '"';
		}

		if (isset($item['color']) && $item['color']) {
			$attr .= ' data-color="' . $item['color'] . '"';
		}

		if (isset($item['bg_color']) && $item['bg_color']) {
			$attr .= ' data-bgcolor="' . $item['bg_color'] . '"';
		}

		$target = $item['target']?'target="' . $item['target'] . '"':'';
		if ($href=='') $href = '#';
		if ($href == '#') $target = '';

		if ($item['name']!='' || $item['icon']) {
			$html .= '<a href="' . $href . '" title="' . strip_tags($item['name']) . '" ' . $target . ' ' . $attr . ' ' . $style . ' ' . $class . '>';
		}

		if ($item['show_icon'] && $item['icon_classes']!='') {
			$html .= '<i class="' .$item['icon_classes'] . '"></i>';
		}

		// Icon Left
		if ($item['show_icon'] && $item['icon_position']=='left' && $item['icon']!='') {
			$html .= '<img class="item-icon icon-left" ';
			if ($item['hover_icon']) $html .= ' data-hoverimg="' . $item['hover_icon'] . '"';
			$html .= ' src="' . $item['icon'] . '" alt="' . strip_tags($item['name']) . '"/>';
		}

		if ($item['name']!='') {
			$html .= '<span>' . $item['name'] . '</span>';
		}

		// Icon Right
		if ($item['show_icon'] && $item['icon_position']=='right' && $item['icon']!='') {
			$html .= '<img class="item-icon icon-right" ';
			if ($item['hover_icon']) $html .= ' data-hoverimg="' . $item['hover_icon'] . '"';
			$html .= ' src="' . $item['icon'] . '" alt="' . strip_tags($item['name']) . '"/>';
		}

		if (isset($item['caret']) && $item['caret']) {
			$html .= '<i class="ves-caret fa ' . $item['caret'] . '"></i>';
		}

		if ($hasChildren) $html .= '<span class="opener"></span>';

		if ($hasChildren) $html .= '<span class="drill-opener"></span>';

		if ($item['name']!='') {
			$html .= '</a>';
		}
		return $html;
	}

	public function drawItem($item, $level = 0, $x = 0, $listTag = true)
	{
		$html = "";
		try{
			$mediaUrl = $this->mediaUrl;
			$hasChildren = false;
			$class = $style = $attr = '';
			if (isset($item['class'])) $class = $item['class'];
			if (!isset($item['status']) || !$item['status']) {
				return;
			}
			if (isset($item['children']) && count($item['children'])>0) $hasChildren = true;

			$class .= ' nav-item level' . $level . ' nav-' . $x;
			// Item Align Type
			if ($item['align'] == '1') {
				$class .= ' submenu-left';
			} else if ($item['align'] == '2') {
				$class .= ' submenu-right';
			} else if ($item['align'] == '3') {
				$class .= ' submenu-alignleft';
			} else if ($item['align'] == '4') {
				$class .= ' submenu-alignright';
			}

			// Group Childs Item
			if ($item['is_group']) {
				$class .= ' subgroup ';
			} else {
				$class .= ' subhover ';
			}

			if ($item['content_type'] == 'dynamic') $class .= ' subdynamic';

			// Disable Dimesion
			if (((int)$item['disable_bellow'])>0)
				$attr .= 'data-disable-bellow="' . $item['disable_bellow'] . '"';

			if ($level==0) {
				$class .=' dropdown level-top';
			} else {
				$class .=' dropdown-submenu';
			}
			$class .= ' ' . $item['classes'];

			// Custom Link, Category Link
			$href = '';
			if ($item['link_type'] == 'custom_link') {
				$href = $item['link'];
			} else if ($item['link_type'] == 'category_link') {
				if ($category = $this->getCategory($item['category'])) {
					$href = $category['url'];
					if($urls = parse_url($href)){
						$url_host = isset($urls['host'])?$urls['host']:"";
						$base_url = $this->_storeManager->getStore()->getBaseUrl();
						if($url_host && ($base_urls = parse_url($base_url))) {
							$base_urls['host'] = isset($base_urls['host'])?$base_urls['host']:"";
							if($url_host != $base_urls['host']){
								$href = str_replace($url_host, $base_urls['host'], $href);
							}
						}
					}
				}
			}

			$link = $this->filter($href);
			$link = trim($link);
			if ($this->endsWith($link, '/')) {
				$link = substr_replace($link, "", -1);	
			}

			if ($hasChildren) {
				$class .= ' parent';
			}

			if ($item['show_icon'] && $item['icon_position']=='left' && $item['icon']!='') {
				$attr .= ' data-hovericon="' . $item['hover_icon'] . '" data-iconsrc="' . $item['icon'] . '"';
			}

			if (isset($item['caret']) && $item['caret']) {
				$attr .= ' data-hovercaret="' . $item['hover_caret'] . '" data-caret="' . $item['caret'] . '"';
			}

			if (isset($item['animation_in']) && $item['animation_in']) {
				$attr .= ' data-animation-in="' . $item['animation_in'] . '"';
			}

			if (isset($item['color']) && $item['color']) {
				$attr .= ' data-color="' . $item['color'] . '"';
			}

			if (isset($item['hover_color']) && $item['hover_color']) {
				$attr .= ' data-hover-color="' . $item['hover_color'] . '"';
			}

			if (isset($item['bg_color']) && $item['bg_color']) {
				$attr .= ' data-bgcolor="' . $item['bg_color'] . '"';
			}

			if (isset($item['bg_hover_color']) && $item['bg_hover_color']) {
				$attr .= ' data-hover-bgcolor="' . $item['bg_hover_color'] . '"';
			}


			if (!isset($item['htmlId'])) {
				$item['htmlId'] = time() . rand();
			}

			if ($listTag) {
				if ($class!='') $class = 'class="' . $class . '"';
				$html = '<li id=' . $item['htmlId'] . ' ' . $class . ' ' . $style . ' ' . $attr . '>';
			} else {
				if (isset($item['dynamic'])) {
					$class .= ' dynamic-item ' . $item['htmlId'];
				}
				if ($class!='') $class = 'class="' . $class . '"';
				$html = '<div id=' . $item['htmlId'] . ' ' . $class . ' ' . $style . ' ' . $attr . '>';
			}

			if (isset($item['before_html']) && $item['before_html']) {
				$html .= '<div class="item-before-content">' . $item['before_html'] . '</div>';
			}

			if (!isset($item['dynamic'])) $html .= $this->drawAnchor($item);
			$tChildren = false;
			if ($item['content_type'] == 'parentcat') {
				$catChildren = $this->getTreeCategories($item['parentcat']);
				if ($catChildren) $tChildren = true;
			}
			if (($item['show_footer'] && $item['footer_html']!='') || ($item['show_header'] && $item['header_html']!='') ||  ($item['show_left_sidebar'] && $item['left_sidebar_html']!='') || ($item['show_right_sidebar'] && $item['right_sidebar_html']!='') || ($item['show_content'] && ((($item['content_type'] == 'childmenu' || $item['content_type'] == 'dynamic') && (isset($item['children']) && count($item['children'])>0)) || ($item['content_type'] == 'content' && $item['content_html']!=''))) || ($item['content_type'] == 'parentcat' && $tChildren) ) {
				$level++;
				$subClass = $subStyle = $subAttr = '';

				if ($item['sub_width']!='') {
					$subStyle .= 'width:' . $item['sub_width'] . ';';
					$subAttr .= 'data-width="' . $item['sub_width'] . '"';
				}

				if (isset($item['dropdown_bgcolor']) && $item['dropdown_bgcolor']) $subStyle .= 'background-color:' . $item['dropdown_bgcolor'] . ';';
				if (isset($item['dropdown_bgimage']) && $item['dropdown_bgimage']) {
					if (!$item['dropdown_bgpositionx']) $item['dropdown_bgpositionx'] = 'center';
					if (!$item['dropdown_bgpositiony']) $item['dropdown_bgpositiony'] = 'center';
					$subStyle .= 'background: url(\'' . trim($item['dropdown_bgimage']) . '\') ' . $item['dropdown_bgimagerepeat'] . ' ' . $item['dropdown_bgpositionx'] . ' ' . $item['dropdown_bgpositiony'] . ' ' . $item['dropdown_bgcolor'] . ';' ;
				}
				if (isset($item['dropdown_inlinecss']) && $item['dropdown_inlinecss']) $subStyle .= $item['dropdown_inlinecss'];

				if (isset($item['dynamic'])) {
					$subClass .= ' content-wrapper';
				}

				if (!isset($item['dynamic'])) {
					$subClass .= ' submenu';
					if ($item['is_group']) {
						$subClass .= ' dropdown-mega';
					} else {
						$subClass .= ' dropdown-menu';
					}
				}

				if (isset($item['animation_in'])) {
					$subClass .= ' animated ';
					$subClass .= $item['animation_in'];
					if ($item['animation_in']) {
						$subAttr .= ' data-animation-in="' . $item['animation_in'] . '"';
					}
					if ($item['animation_time']) {
						$subStyle .= 'animation-duration: ' . $item['animation_time'] . 's;-webkit-animation-duration: ' . $item['animation_time'] . 's;';
					}
				}

				if ($subClass!='') $subClass = 'class="' . $subClass . '"';
				if ($subStyle!='') $subStyle = 'style="' . $subStyle . '"';

				if (!isset($item['dynamic'])) {
					$html .= '<div ' . $subClass . ' ' . $subAttr . ' ' . $subStyle . '>';
				}

				$html .= '<div class="drilldown-back"><a href="#"><span class="drill-opener"></span><span class="current-cat"></span></a></div>';

				$html .= '<div class="submenu-inner">';

				// TOP BLOCK
				if ($item['show_header'] && $item['header_html']!='') {
					$html .= '<div class="item-header">' . $this->decodeWidgets($item['header_html']) . '</div>';
				}

				if ($item['show_left_sidebar'] || $item['show_content'] || $item['show_right_sidebar']) {

					if (!isset($item['dynamic'])) {
						$html .= '<div class="content-wrapper">';
					} else {
						$html .= '<div ' . $subClass . ' ' . $subAttr . ' ' . $subStyle . '>';
					}

					$left_sidebar_width  = isset($item['left_sidebar_width'])?$item['left_sidebar_width']:0;
					$content_width       = $item['content_width'];
					$right_sidebar_width = isset($item['right_sidebar_width'])?$item['right_sidebar_width']:0;

					// LEFT SIDEBAR BLOCK
					if ($item['show_left_sidebar'] && $item['left_sidebar_html']!='') {
						if ($left_sidebar_width) $left_sidebar_width = 'style="width:' . $left_sidebar_width . '"';

						$html .= '<div class="item-sidebar left-sidebar" ' . $left_sidebar_width . '>' . $this->decodeWidgets($item['left_sidebar_html']) . '</div>';
					}
					// MAIN CONTENT BLOCK
					if ($item['show_content'] && ((($item['content_type'] == 'childmenu' || $item['content_type'] == 'dynamic') && $hasChildren) || $item['content_type'] == 'parentcat' || ($item['content_type'] == 'content' && $item['content_html']!=''))) {

						$html .= '<div class="item-content" ' . ($content_width==''?'':'style="width:' . $content_width . '"') . '>';

						// Content HTML
						if ($item['content_type'] == 'content' && $item['content_html']!='') {
							$html .= '<div class="nav-dropdown">' . $this->decodeWidgets($item['content_html']) . '</div>';
						}

						// Dynamic Tab
						if ($item['content_type'] == 'dynamic' && $hasChildren) {
							$column = (int)$item['child_col'];
							$html .= '<div class="level' . $level . ' nav-dropdown ves-column' . $column . '">';
							$children = $item['children'];
							$i = $z = 0;
							$total = count($children);

							$html .= '<div class="dorgin-items ' . (isset($item['tab_position'])?'dynamic-' . $item['tab_position']:'') . ' row hidden-sm hidden-xs">';

							$html .= '<div class="dynamic-items ';
							if (!isset($item['tab_position']) || (isset($item['tab_position']) && $item['tab_position'] != 'top')) {
								$html .= 'col-xs-3';
							}
							
							$html .= ' hidden-xs hidden-sm">';

							$html .= '<ul>';
							foreach ($children as $it) {
								$attr1 = '';
								if ($it['show_icon'] && $it['icon_position']=='left' && $it['icon']!='') {
									$attr1 .= ' data-hovericon="' . $it['hover_icon'] . '" data-iconsrc="' . $it['icon'] . '"';
								}

								if ($it['caret']) {
									$attr1 .= ' data-hovercaret="' . $it['hover_caret'] . '" data-caret="' . $it['caret'] . '"';
								}

								$iClass = 'nav-item';
								if ($z==0) {
									$iClass .= ' dynamic-active';
								}
								if ($iClass) {
									$iClass = 'class="' . $iClass . '"';
								}
								$html .= '<li ' . $iClass . ' data-dynamic-id="' . $it['htmlId'] . '" ' . $attr1 . '>';
								$html .= $this->drawAnchor($it, $level);
								$html .= '</li>';
								$i++;
								$z++;
							}

							$html .= '</ul>';
							$html .= '</div>';
							$html .= '<div class="dynamic-content ';
							if (!isset($item['tab_position']) ||  (isset($item['tab_position']) && $item['tab_position'] != 'top')) {
								$html .= 'col-xs-9';
							}
							$html .= ' hidden-xs hidden-sm">';

							$z = 0;
							foreach ($children as $it) {
								if ($z==0) {
									$it['class'] = 'dynamic-active';
								}
								$it['dynamic'] = true;
								$html .= $this->filter($this->drawItem($it, $level, $i, false));
								$i++;
								$z++;
							}
							$html .= '</div>';
							$html .= '</div>';


							$html   .= '<div class="orgin-items hidden-lg hidden-md">';
							$i      = 0;
							$column = 1;
							foreach ($children as $it) {
								$html .= '<div class="mega-col col-sm-' . (12/$column) . ' mega-col-' . $i . ' mega-col-level-' . $level . '">';
								$html .= $this->filter($this->drawItem($it, $level, $i, false));
								$html .= '</div>';
								$i++;
							}
							$html .= '</div>';


							$html .= '</div>';
						}

						// Child item
						if ($item['content_type'] == 'childmenu' && $hasChildren) {
							$column   = (int)$item['child_col'];
							$html     .= '<div class="level' . $level . ' nav-dropdown ves-column' . $column . '">';
							$children = $item['children'];
							$i        = 0;
							$total    = count($children);
							
							$resultTmp = [];
							$x1 = 0;
							$levelTmp =1;
							foreach ($children as  $z => $it) {
								$resultTmp[$x1][$levelTmp] = $this->drawItem($it, $level, $i, false);
								if ($x1==$column-1 || $i == (count($children)-1)) {
									$levelTmp++;
									$x1=0;
								} else {
									$x1++;
								}
								$i++;
							}
							$html .= '<div class="item-content1 hidden-xs hidden-sm">';
							foreach ($resultTmp as $k1 => $v1) {
								$html .= '<div class="mega-col mega-col-' . $i . ' mega-col-level-' . $level . ' col-xs-12">';
								foreach ($v1 as $k2 => $v2) {
									$html .= $v2;
								}
								$html .= '</div>';
							}
							$html .= '</div>';
							$html .= '<div class="item-content2 hidden-lg hidden-md">';
							foreach ($children as  $z => $it) {
								$html .= $this->filter($this->drawItem($it, $level, $i, false));
							}
							$html .= '</div>';
							$html .= '</div>';
						}

						// Child item
						if ($item['content_type'] == 'parentcat') {
							$html .= '<div class="level' . $level . ' nav-dropdown">';

							$catChildren = $this->getTreeCategories($item['parentcat']);

							$i = 0;
							$total = count($catChildren);
							$column = (int)$item['child_col'];
							foreach ($catChildren as $it) {
								if ($column == 1 || $i%$column == 0) {
									$html .= '<div class="row">';
								}
								$html .= '<div class="mega-col col-sm-' . (12/$column) . ' mega-col-' . $i . ' mega-col-level-' . $level . ' col-xs-12">';
								$html .= $this->drawItem($it, $level, $i, false);
								$html .= '</div>';
								if ($column == 1 || ($i+1)%$column == 0 || $i == ($total-1)) {
									$html .= '</div>';
								}
								$i++;
							}
							$html .= '</div>';

						}

						$html .= '</div>';
					}

					// RIGHT SIDEBAR BLOCK
					if ($item['show_right_sidebar'] && $item['right_sidebar_html']!='') {
						if ($right_sidebar_width) $right_sidebar_width = 'style="width:' . $right_sidebar_width . '"';
						$html .= '<div class="item-sidebar right-sidebar" ' . $right_sidebar_width . '>' . $this->decodeWidgets($item['right_sidebar_html']) . '</div>';
					}

					$html .= '</div>';
				}

				// BOOTM BLOCK
				if ($item['show_footer'] && $item['footer_html']!='') {
					$html .= '<div class="item-footer">' . $this->decodeWidgets($item['footer_html']) . '</div>';
				}

				$html .= '</div>';

				if (!isset($item['dynamic'])) {
					$html .= '</div>';
				}
			}

			if (isset($item['after_html']) && $item['after_html']) {
				$html .= '<div class="item-after-content">' . $item['after_html'] . '</div>';
			}

			if ($listTag) {
				$html .= '</li>';
			} else {
				$html .= '</div>';	
			}
		} catch (\Exception $e) {

		}
		return $html;
	}

	public function decodeImg($str) {
		$orginalStr    = $str;
		$count         = substr_count($str, "<img");
		$mediaUrl      = $this->mediaUrl;
		$firstPosition = 0;
		for ($i=0; $i < $count; $i++) {
			if ($firstPosition==0) $tmp = $firstPosition;
			if ($tmp>strlen($str)) continue;
			$firstPosition = strpos($str, "<img", $tmp);
			$nextPosition = strpos($str, "/>", $firstPosition);
			$tmp = $nextPosition;
			if (!strpos($str, "<img")) continue;
			$length = $nextPosition - $firstPosition;
			$img = substr($str, $firstPosition, $length+2);
			if (!strpos($img, $this->_storeManager->getStore()->getBaseUrl())) {
				continue;
			}

			$newImg = $this->filter($img);
			$f = strpos($newImg, 'src="', 0)+5;
			$n = strpos($newImg, '"', $f+5);
			$src = substr($newImg, $f, ($n-$f));
			if (!strpos($img, 'placeholder.gif')) {
				$src1 = '';
				if (strpos($newImg, '___directive')) {
					$e = strpos($newImg, '___directive', 0) + 13;
					$e1 = strpos($newImg, '/key', 0);
					$src1 = substr($newImg, $e, ($e1-$e));
					$src1 = base64_decode($src1);
				} else {
					$mediaP = strpos($src, "wysiwyg", 0);
					$src1 = substr($src, $mediaP);
					$src1 = '{{media url="' . $src1 . '"}}';
				}
				$orginalStr = str_replace($src, $src1, $orginalStr);
				$newImg = str_replace($src, $src1, $newImg);
			}
		}
		return $orginalStr;
	}

	public function getRootCategory() {
		if (!$this->_catsCollection) {
			$menuCategories = $this->getMenuCategories(); 
			$cats   = $this->storeCategories->getCategoriesCollection($menuCategories, null, $this->_storeManager->getStore()->getId());
			$rootId = $this->_storeManager->getStore()->getRootCategoryId();

			if ($cats) {
				foreach ($cats as $cat) {
					if ($cat['value'] == $rootId) {
						$this->_catsCollection = $cat;
						break;
					}
				}
			} else {
				$this->_catsCollection = [];
			}
		}
		return $this->_catsCollection;
	}

	public function getAllCategory() {
		if (!$this->_cats) {
			$this->_cats = $this->_categoryFactory->create()->getCollection()
			->addAttributeToSelect('*')
			->addAttributeToFilter('is_active','1')
			->addAttributeToSort('position', 'asc');
		}
		return $this->_cats;
	}

	public function getCategory($catId, $cat = '') {
		$catId = (int) $catId;
		if ($cat == '') {
			$cat = $this->getRootCategory();
		}
		$category = '';
		if ($cat) {
			if ((int) $cat['value'] == (int) $catId) {
				return $cat['category'];
			} else if (isset($cat['children']) && is_array($cat['children'])) {
				foreach ($cat['children'] as $catChild) {
					$category = $this->getCategory($catId, $catChild);
					if ($category) {
						break;
					}
				}
			}
		}
		return $category;
	}

	public function getTreeCategories($parentId, $level = 0, $list = []) {
		$category = $this->getCategory($parentId);
		$cats     = $this->getAllCategory();
		foreach($cats as $category) {
			if ($category->getParentId() == $parentId) {
				$tmp                   = [];
				$tmp["name"]           = $category->getName();
				$tmp['link_type']      = 'custom_link';
				$tmp['link']           = $category->getUrl();
				$tmp['show_footer']    = $tmp['show_header'] = $tmp['show_left_sidebar'] = $tmp['show_right_sidebar'] = 0;
				$tmp['show_content']   = 1;
				$tmp['content_width']  = $tmp['sub_width'] = '100%';
				$tmp['color']          = '';
				$tmp['show_icon']      = $tmp['is_group'] = false;
				$tmp['content_type']   = 'childmenu';
				$tmp['target']         = '_self';
				$tmp['align']          = 3;
				$tmp['child_col']      = 1;
				$tmp['status']         = 1;
				$tmp['disable_bellow'] = 0;
				$tmp['classes']        = '';
				$tmp['id']             = $category->getId();
				$tmp['children']       = $this->getTreeCategories($category->getId(),(int)$level + 1);
				$list[] = $tmp;
			}
		}
		return $list;
	}

	public function getCurrentUrl() {
        $currentUrl = $this->_url->getCurrentUrl();
        $currentUrl = explode("?", $currentUrl);
        $currentUrl = $currentUrl[0];
        if ($this->endsWith($currentUrl, "/")) {
        	$currentUrl = substr_replace($currentUrl, "", -1);
        }
        return $currentUrl;
	}

	public function endsWith($haystack, $needle) {
		return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
	}

    /**
     * Escape quotes in java script
     *
     * @param  mixed  $data
     * @param  string $quote
     * @return mixed
     */
    public function jsQuoteEscape($data, $quote='\'')
    {
    	if (is_array($data)) {
    		$result = [];
    		foreach ($data as $item) {
    			$result[] = str_replace($quote, '\\'.$quote, $item);
    		}

    		return $result;
    	}

    	return str_replace($quote, '\\'.$quote, $data);

    }//end jsQuoteEscape()

    public function getMenuCategories() {
    	return $this->menuCategories;
    }

    public function setMenuCategories($categories) {
    	$this->menuCategories = $categories;
    	return $this;
    }
}
