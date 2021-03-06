<?php
class Mage_Uploadtool_IndexController extends Mage_Core_Controller_Front_Action
{
	
	const XML_PATH_EMAIL_RECIPIENT  = 'uploadtool/email/recipient_email';
	const XML_PATH_EMAIL_BCC  = 'uploadtool/email/bcc_email';
	const XML_PATH_EMAIL_SENDER     = 'uploadtool/email/sender_email_identity';
	const XML_PATH_EMAIL_TEMPLATE   = 'uploadtool/email/email_template';
	const XML_PATH_EMAIL_TEMPLATE_USER   = 'uploadtool/email/email_template_user';
	
	
    public function indexAction() {
		$this->loadLayout();     
		$this->renderLayout();
    }
	
	public function addtocartAction()
    {
		$resource = Mage::getConfig()->getNode('global/resources')->asArray();
		$magento_db = $resource['default_setup']['connection']['host'];
		$mdb_user = $resource['default_setup']['connection']['username'];
		$mdb_passwd = $resource['default_setup']['connection']['password'];
		$mdb_name = $resource['default_setup']['connection']['dbname'];
		$magento_connection = @mysql_connect($magento_db, $mdb_user, $mdb_passwd);
		
		if (!$magento_connection)
		{
			die('Unable to connect to the database');
		}
		@mysql_select_db($mdb_name, $magento_connection) or die ("Database not found.");
		
		$id = $this->getRequest()->getParam('id');
		$sku = $this->getRequest()->getParam('lotno');
		
		if($sku == '')
		{
			Mage::getSingleton("catalog/session")->addError('No Diamond Found.');
			$this->_redirectReferer();
			return;
		}
		
		$tableName = Mage::getSingleton("core/resource")->getTableName('uploadtool_diamonds_inventory');
		
		$sql = 'select * from '.$tableName.' where `id` = "'.$id.'"';
		$itemNum = $sku;
		$result = mysql_query($sql) or die(mysql_error());
			while($row = mysql_fetch_array($result))
			{
				$title = $row['shape'];
				$carat = number_format($row['carat'], 2, '.', '');
				$productName = $sku . " " . $row['shape'] . " " . $carat . " " . $row['color'] . " " . $row['clarity'];
				
				$description = 'Shape: '.$row['shape'].'<br />'.'Carat:'.$carat.'<br />'.'Color:'.$row['color'].'<br />'.'Clarity :'.$row['clarity'];
				
				if(isset($row['cut']) && $row['cut'] != "") {
					$description .= "<br />Cut: ".$row['cut'];
				}
				if(isset($row['culet']) && $row['culet'] != "") {
					$description .= "<br />Culet: ".$row['culet'];
				}
				if(isset($row['crown']) && $row['crown'] != "") {
					$description .= "<br />Crown: ".$row['crown'];
				}
				if(isset($row['pavilion']) && $row['pavilion'] != "") {
					$description .= "<br />Pavilion: ".$row['pavilion'];
				}
				if(isset($row['dimensions']) && $row['dimensions'] != "") {
					$description .= "<br />Dimensions: ".$row['dimensions'];
				}
				if(isset($row['depth']) && $row['depth'] != "") {
					$description .= "<br />Depth: ".$row['depth'];
				}
				if(isset($row['tabl']) && $row['tabl'] != "") {
					$description .= "<br />Table: ".$row['tabl'];
				}
				if(isset($row['polish']) && $row['polish'] != "") {
					$description .= "<br />Polish: ".$row['polish'];
				}
				if(isset($row['symmetry']) && $row['symmetry'] != "") {
					$description .= "<br />Symmetry: ".$row['symmetry'];
				}
				if(isset($row['fluorescence']) && $row['fluorescence'] != "") {
					$description .= "<br />Fluorescence: ".$row['fluorescence'];
				}
				
				if(isset($row['girdle']) && $row['girdle'] != "") {
					$description .= "<br />Girdle: ".$row['girdle'];
				}
				if(isset($row['certificate']) && $row['certificate'] != "") {
					$description .= "<br />Certificate: ".$row['certificate'];
				}
				if(isset($row['fancy_intensity']) && $row['fancy_intensity'] != "") {
					$description .= "<br />Fancy Intensity: ".$row['fancy_intensity'];
				}
				if(isset($row['fancycolor']) && $row['fancycolor'] != "") {
					$description .= "<br />Fancy Color: ".$row['fancycolor'];
				}
				if(isset($row['cert_number']) && $row['cert_number'] != "") {
					$description .= "<br />Certificate Number: ".$row['cert_number'];
				}
				if(isset($row['availability']) && $row['availability'] != "") {
					$description .= "<br />Availability: ".$row['availability'];
				}
				
				$price = $row['totalprice'];
				
				$url_key = $sku. "-shape-".$row['shape']."-carat-".$carat."-color-".$row['color']."-clarity-".$row['clarity'];
			}
		$storeId = Mage::app()->getStore()->getId();
		
		
	    $id = Mage::getModel('catalog/product')->getIdBySku($itemNum);
		if ($id) {
			$product = Mage::getModel('catalog/product')->load($id);
			//echo count($product->getMediaGalleryImages()); exit;
			//echo "<pre>"; print_r($product->getMediaGalleryImages()); exit;
		} else {
			$product = Mage::getModel('catalog/product');
		}
		
		$product->setTypeId('simple');  //
		$product->setTaxClassId(2); //none
		$product->setWebsiteIds(array(1));  // store id
		$product->setAttributeSetId(9); //Videos Attribute Set Id
	
		$product->setSku($itemNum);
		$product->setName($productName);
		$product->setDescription($description);
		$product->setPrice($price);
		$product->setShortDescription(ereg_replace("\n","",$description));
		$product->setWeight(0);
		$product->setStatus(1); //enabled
		$product->setVisibilty(4); //catalog and search
		$product->setMetaDescription(ereg_replace("\n","",$description));
		$product->setMetaTitle($productName);	
		
		$product->setUrlKey($url_key); // make proper urls for product
		
		$gallery_img = '/shape/'.strtolower($title).'.jpg';
		
		if (!$id) {
			$product->addImageToMediaGallery(Mage::getBaseDir('media') . DS . 'import' . $gallery_img, array('small_image','thumbnail','image'), false,true);
		}
		
		$product->save(); 

		$stockItem = Mage::getModel('cataloginventory/stock_item');
		$stockItem->loadByProduct($product->getId());
		
		if (! $stockItem->getId()) {
			$stockItem->setProductId($product->getId())->setStockId(1);
		}
		
		$stockItem->setData('is_in_stock',1);
		$stockItem->save();

		$stockItem->loadByProduct($product->getId());			
		$stockItem->setData('qty', 1);
		$stockItem->save();
		
		$id = $product->getId();
		
//		Mage::getSingleton("catalog/session")->addSuccess($e->getMessage());
		$this->_redirect("checkout/cart/add", array('product'=>$id,'qty'=>1,'form_key'=>Mage::getSingleton('core/session')->getFormKey()));
		return;
	}
	
	public function addtowishlistAction()
	{
		$resource = Mage::getConfig()->getNode('global/resources')->asArray();
		$magento_db = $resource['default_setup']['connection']['host'];
		$mdb_user = $resource['default_setup']['connection']['username'];
		$mdb_passwd = $resource['default_setup']['connection']['password'];
		$mdb_name = $resource['default_setup']['connection']['dbname'];
		$magento_connection = @mysql_connect($magento_db, $mdb_user, $mdb_passwd);
	
		if (!$magento_connection)
		{
			die('Unable to connect to the database');
		}
		@mysql_select_db($mdb_name, $magento_connection) or die ("Database not found.");
	
	
		$sku = $this->getRequest()->getParam('id');
	
		if($sku == '') {
			Mage::getSingleton("catalog/session")->addError('No Diamond Found.');
			//$this->_redirect("diamond-search.html");
			$this->_redirectReferer();
			return;
		}
	
		$tableName = Mage::getSingleton("core/resource")->getTableName('uploadtool_diamonds_inventory');
	
		$sql = 'select * from '.$tableName.' where `id` = "'.$sku.'"';
	
		//echo $sql; exit;
		$itemNum = $sku;
		$result = mysql_query($sql) or die(mysql_error());
	
		$total = mysql_num_rows($result);
	
	$url_key = "";
	$rewriteUrl = "";
	
		if($total > 0) {
			while($row = mysql_fetch_array($result)) {
				$title = Mage::helper("diamondsearch")->getSpecialAbbrnew($row['shape']); //$row['shape'];
				$carat = number_format($row['carat'], 2, '.', '');
				$productName = $sku . " " . $row['shape'] . " " . $carat . " " . $row['color'] . " " . $row['clarity'];
				
				$description = 'Shape: '.$row['shape'].'<br />'.'Carat:'.$carat.'<br />'.'Color:'.$row['color'].'<br />'.'Clarity :'.$row['clarity'];
				
				if(isset($row['cut']) && $row['cut'] != "") {
					$description .= "<br />Cut: ".$row['cut'];
				}
				if(isset($row['culet']) && $row['culet'] != "") {
					$description .= "<br />Culet: ".$row['culet'];
				}
				if(isset($row['crown']) && $row['crown'] != "") {
					$description .= "<br />Crown: ".$row['crown'];
				}
				if(isset($row['pavilion']) && $row['pavilion'] != "") {
					$description .= "<br />Pavilion: ".$row['pavilion'];
				}
				if(isset($row['dimensions']) && $row['dimensions'] != "") {
					$description .= "<br />Dimensions: ".$row['dimensions'];
				}
				if(isset($row['depth']) && $row['depth'] != "") {
					$description .= "<br />Depth: ".$row['depth'];
				}
				if(isset($row['tabl']) && $row['tabl'] != "") {
					$description .= "<br />Table: ".$row['tabl'];
				}
				if(isset($row['polish']) && $row['polish'] != "") {
					$description .= "<br />Polish: ".$row['polish'];
				}
				if(isset($row['symmetry']) && $row['symmetry'] != "") {
					$description .= "<br />Symmetry: ".$row['symmetry'];
				}
				if(isset($row['fluorescence']) && $row['fluorescence'] != "") {
					$description .= "<br />Fluorescence: ".$row['fluorescence'];
				}
				
				if(isset($row['girdle']) && $row['girdle'] != "") {
					$description .= "<br />Girdle: ".$row['girdle'];
				}
				if(isset($row['certificate']) && $row['certificate'] != "") {
					$description .= "<br />Certificate: ".$row['certificate'];
				}
				if(isset($row['fancy_intensity']) && $row['fancy_intensity'] != "") {
					$description .= "<br />Fancy Intensity: ".$row['fancy_intensity'];
				}
				if(isset($row['fancycolor']) && $row['fancycolor'] != "") {
					$description .= "<br />Fancy Color: ".$row['fancycolor'];
				}
				if(isset($row['cert_number']) && $row['cert_number'] != "") {
					$description .= "<br />Certificate Number: ".$row['cert_number'];
				}
				if(isset($row['availability']) && $row['availability'] != "") {
					$description .= "<br />Availability: ".$row['availability'];
				}
				
				$price = $row['totalprice'];
				$productimage=$row['diamond_image']; 
				
				$url_key = strtolower( $sku. "-shape-".$row['shape']."-carat-".$carat."-color-".$row['color']."-clarity-".$row['clarity'] );
				$rewriteUrl = Mage::helper("diamondsearch")->getDiamondLink($carat,$row['shape'],$row['cut'],$row['certificate'],$row['color'],$row['clarity'],$row['lotno'],$row['cert_number']);
				

				}
		}
	
		$storeId = Mage::app()->getStore()->getId();
	
		$product = Mage::getModel('catalog/product');
		$product->setTypeId('simple');  //
		$product->setTaxClassId(0); //none
		$product->setWebsiteIds(array(1));  // store id
		$product->setAttributeSetId(9); //Videos Attribute Set Id
			
		$product->setSku($itemNum);
		$product->setName($title);
		$product->setDescription($description);
		$product->setPrice($price);
		$product->setShortDescription(ereg_replace("\n","",$description));
		$product->setWeight(0);
		$product->setStatus(1); //enabled
		$product->setVisibilty(4); //catalog and search
		$product->setMetaDescription(ereg_replace("\n","",$description));
		$product->setMetaTitle($title);
		$product->setUrlKey($url_key); // make proper urls for product
	
		$file_headers = @get_headers($productimage);
		if($file_headers[0] != 'HTTP/1.1 404 Not Found' && $productimage!='') 
		{		 
			 
			$url=Mage::getBaseDir('media');
			if (!file_exists('path/to/directory')) 	{    mkdir($url.'/import/shape/actual', 0777, true);}
			$url=$url.'/import/shape/actual/'.basename($productimage);
			file_put_contents($url, file_get_contents($productimage));
			$gallery_img = '/shape/actual/'.basename($productimage); 
			if (!$id) {
				$product->addImageToMediaGallery(Mage::getBaseDir('media') . DS . 'import' . $gallery_img, array('small_image','thumbnail','image'), false,true);
			} 				
	    }
	    else
	    {				
			$gallery_img = '/shape/'.strtolower($title).'.jpg';
		
			if (!$id) {
				$product->addImageToMediaGallery(Mage::getBaseDir('media') . DS . 'import' . $gallery_img, array('small_image','thumbnail','image'), false,true);
			}	
		}	
		//$product->addImageToMediaGallery(Mage::getBaseDir('media') . DS . 'import' . $gallery_img, array('small_image','thumbnail','image'), false,true);
		
		$product->save();

		/** @var Mage_Core_Model_Url_Rewrite $rewrite */
		$rewrite = Mage::getModel('core/url_rewrite');
		$allStores = Mage::app()->getStores();
	 
		// Check for existing rewrites:
		foreach($allStores as $store)
		{
			$storeId = $store->getId();			
			// Attempt loading it first, to prevent duplicates:
			
			$idPath = "product/".$product->getId();
			
			$rewrite->loadByIdPath($idPath);
			$rewrite->setStoreId($storeId);
			$rewrite->setOptions('RP');
			$rewrite->setIdPath($idPath);
			$rewrite->setRequestPath($url_key);
			$rewrite->setIsSystem(0);
			$rewrite->setTargetPath($rewriteUrl);
	 
			$rewrite->save();
		}
		
	
		$stockItem = Mage::getSingleton('cataloginventory/stock_item');
		$stockItem->loadByProduct($product->getId());
	
		if (! $stockItem->getId()) {
			$stockItem->setProductId($product->getId())->setStockId(1);
		}
	
		$stockItem->setData('is_in_stock',1);
		$stockItem->save();
	
		$stockItem->loadByProduct($product->getId());
		$stockItem->setData('qty', 1);
		$stockItem->save();
	
		
	
	
	
		$id = $product->getId();
		//$_product = Mage::getSingleton('catalog/product')->load($id);
		//$wishUrl = Mage::helper("wishlist")->getAddUrl($_product);
		$this->_redirect("wishlist/index/add",array('product'=>$id,'form_key'=>Mage::getSingleton('core/session')->getFormKey()));
		return;
	
	}
	
	
	/*public function addtowishlistAction()
	{
		$resource = Mage::getConfig()->getNode('global/resources')->asArray();
		$magento_db = $resource['default_setup']['connection']['host'];
		$mdb_user = $resource['default_setup']['connection']['username'];
		$mdb_passwd = $resource['default_setup']['connection']['password'];
		$mdb_name = $resource['default_setup']['connection']['dbname'];
		$magento_connection = @mysql_connect($magento_db, $mdb_user, $mdb_passwd);
	
		if (!$magento_connection)
		{
			die('Unable to connect to the database');
		}
		@mysql_select_db($mdb_name, $magento_connection) or die ("Database not found.");
	
	
		$sku = $this->getRequest()->getParam('stock_num');
	
		if($sku == '') {
			Mage::getSingleton("catalog/session")->addError('No Diamond Found.');
			//$this->_redirect("diamond-search.html");
			$this->_redirectReferer();
			return;
		}
	
		$tableName = Mage::getSingleton("core/resource")->getTableName('uploadtool_diamonds_inventory');
	
		$sql = 'select * from '.$tableName.' where `lotno` = "'.$sku.'"';
	
		//echo $sql; exit;
		$itemNum = $sku;
		$result = mysql_query($sql) or die(mysql_error());
	
		$total = mysql_num_rows($result);
	
		if($total > 0) {
			while($row = mysql_fetch_array($result)) {
				$title = $row['shape'];
				$carat = number_format($row['carat'], 2, '.', '');
				$productName = $sku . " " . $row['shape'] . " " . $carat . " " . $row['color'] . " " . $row['clarity'];
				
				$description = 'Shape: '.$row['shape'].'<br />'.'Carat:'.$carat.'<br />'.'Color:'.$row['color'].'<br />'.'Clarity :'.$row['clarity'];
				
				if(isset($row['cut']) && $row['cut'] != "") {
					$description .= "<br />Cut: ".$row['cut'];
				}
				if(isset($row['culet']) && $row['culet'] != "") {
					$description .= "<br />Culet: ".$row['culet'];
				}
				if(isset($row['crown']) && $row['crown'] != "") {
					$description .= "<br />Crown: ".$row['crown'];
				}
				if(isset($row['pavilion']) && $row['pavilion'] != "") {
					$description .= "<br />Pavilion: ".$row['pavilion'];
				}
				if(isset($row['dimensions']) && $row['dimensions'] != "") {
					$description .= "<br />Dimensions: ".$row['dimensions'];
				}
				if(isset($row['depth']) && $row['depth'] != "") {
					$description .= "<br />Depth: ".$row['depth'];
				}
				if(isset($row['tabl']) && $row['tabl'] != "") {
					$description .= "<br />Table: ".$row['tabl'];
				}
				if(isset($row['polish']) && $row['polish'] != "") {
					$description .= "<br />Polish: ".$row['polish'];
				}
				if(isset($row['symmetry']) && $row['symmetry'] != "") {
					$description .= "<br />Symmetry: ".$row['symmetry'];
				}
				if(isset($row['fluorescence']) && $row['fluorescence'] != "") {
					$description .= "<br />Fluorescence: ".$row['fluorescence'];
				}
				
				if(isset($row['girdle']) && $row['girdle'] != "") {
					$description .= "<br />Girdle: ".$row['girdle'];
				}
				if(isset($row['certificate']) && $row['certificate'] != "") {
					$description .= "<br />Certificate: ".$row['certificate'];
				}
				if(isset($row['fancy_intensity']) && $row['fancy_intensity'] != "") {
					$description .= "<br />Fancy Intensity: ".$row['fancy_intensity'];
				}
				if(isset($row['fancycolor']) && $row['fancycolor'] != "") {
					$description .= "<br />Fancy Color: ".$row['fancycolor'];
				}
				if(isset($row['cert_number']) && $row['cert_number'] != "") {
					$description .= "<br />Certificate Number: ".$row['cert_number'];
				}
				if(isset($row['availability']) && $row['availability'] != "") {
					$description .= "<br />Availability: ".$row['availability'];
				}
				
				$price = $row['totalprice'];
				
				$url_key = $sku. "-shape-".$row['shape']."-carat-".$carat."-color-".$row['color']."-clarity-".$row['clarity'];
			}
		}
	
		$storeId = Mage::app()->getStore()->getId();
	
		$product = Mage::getModel('catalog/product');
		$product->setTypeId('simple');  //
		$product->setTaxClassId(0); //none
		$product->setWebsiteIds(array(1));  // store id
		$product->setAttributeSetId(9); //Videos Attribute Set Id
			
		$product->setSku($itemNum);
		$product->setName($title);
		$product->setDescription($description);
		$product->setPrice($price);
		$product->setShortDescription(ereg_replace("\n","",$description));
		$product->setWeight(0);
		$product->setStatus(1); //enabled
		$product->setVisibilty(4); //catalog and search
		$product->setMetaDescription(ereg_replace("\n","",$description));
		$product->setMetaTitle($title);
	
		$gallery_img = '/shape/'.strtolower($title).'.jpg';
	
		$product->addImageToMediaGallery(Mage::getBaseDir('media') . DS . 'import' . $gallery_img, array('small_image','thumbnail','image'), false,true);
		$product->save();
	
	
		$stockItem = Mage::getSingleton('cataloginventory/stock_item');
		$stockItem->loadByProduct($product->getId());
	
		if (! $stockItem->getId()) {
			$stockItem->setProductId($product->getId())->setStockId(1);
		}
	
		$stockItem->setData('is_in_stock',1);
		$stockItem->save();
	
		$stockItem->loadByProduct($product->getId());
		$stockItem->setData('qty', 1);
		$stockItem->save();
	
		$id = $product->getId();
		$_product = Mage::getSingleton('catalog/product')->load($id);
		//$wishUrl = Mage::helper("wishlist")->getAddUrl($_product);
		$this->_redirect("wishlist/index/add",array('product'=>$id,'form_key'=>Mage::getSingleton('core/session')->getFormKey()));
		return;
	
	}*/
	
	public function requestPostAction() {
		
		$post = $this->getRequest()->getPost();

		if ( $post ) {
			
			
			$model = Mage::getModel('uploadtool/diamondinquiries');
			$model->setFirstname($post['firstname']);
			$model->setLastname($post['lastname']);
			$model->setPhone($post['phone']);
			$model->setEmail($post['email']);
			$model->setComment($post['comments']);
			$model->setCreatedTime(now());
			$model->save();
			
	 
			
			$translate = Mage::getSingleton('core/translate');
			/* @var $translate Mage_Core_Model_Translate */
			$translate->setTranslateInline(false);
			
			try {
				
				$resource = Mage::getConfig()->getNode('global/resources')->asArray();
				$magento_db = $resource['default_setup']['connection']['host'];
				$mdb_user = $resource['default_setup']['connection']['username'];
				$mdb_passwd = $resource['default_setup']['connection']['password'];
				$mdb_name = $resource['default_setup']['connection']['dbname'];
				$magento_connection = @mysql_connect($magento_db, $mdb_user, $mdb_passwd);
				
				if (!$magento_connection)
				{
					die('Unable to connect to the database');
				}
				@mysql_select_db($mdb_name, $magento_connection) or die ("Database not found.");
				
				if($post['stock_number'] == ''){
					Mage::getSingleton("catalog/session")->addError('No Diamond Found.');
					$this->_redirectUrl($_POST['current_url']);
					return;
				}
				
				$tableName = Mage::getSingleton("core/resource")->getTableName('uploadtool_diamonds_inventory');
				$sql = 'select * from '.$tableName.' where `lotno` = "'.$post['stock_number'].'"';
				$result = mysql_query($sql) or die(mysql_error());
				
				$cfp_data = Mage::helper("diamondsearch")->getCallForPriceData();
				$cfpEanble = $cfp_data["is_cfp"];
				$cfpMessage = $cfp_data["cfp_msg"];
				
				while($row = mysql_fetch_array($result))
				{
					$post['lotno'] = $row['lotno'];
					$post['owner'] = $row['owner'];
					$post['shape'] = $row['shape'];
					$post['carat'] = $row['carat'];
					$post['color'] = $row['color'];
					$post['clarity'] = $row['clarity'];
					$post['cut'] = $row['cut'];
					$post['culet'] = $row['culet'];
					$post['crown'] = $row['crown'];
					$post['pavilion'] = $row['pavilion'];
					$post['dimensions'] = $row['dimensions'];
					$post['depth'] = $row['depth'];
					$post['tabl'] = $row['tabl'];
					$post['polish'] = $row['polish'];
					$post['symmetry'] = $row['symmetry'];
					$post['fluorescence'] = $row['fluorescence'];
					$post['flour_intensity'] = $row['flour_intensity'];
					$post['girdle'] = $row['girdle'];
					$post['certificate'] = $row['certificate'];
					$post['fancy_intensity'] = $row['fancy_intensity'];
					$post['fancycolor'] = $row['fancycolor'];
					$post['percent_rap'] = $row['percent_rap'];
					
					if($cfpEanble && $cfpEanble == 1)
						$post['totalprice'] = $cfpMessage;
					else
						$post['totalprice'] = $row['totalprice'];
					
					$post['Lab'] = $row['Lab'];
					$post['cert_number'] = $row['cert_number'];
					$post['make'] = $row['make'];
				}

				$ch = curl_init();
				$timeout = 5;
				curl_setopt($ch,CURLOPT_URL,"http://www.jewelerslink.com/uploader/index/loadCustomerByVendor");
				curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
				curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
				curl_setopt($ch, CURLOPT_POSTFIELDS, array("vendor"=>$post['owner']));
				$data = curl_exec($ch);
				curl_close($ch);				
				//echo $data; exit;	
				$vendorData = json_decode($data, true);				
				//echo "<pre>"; print_r($vendorData);exit;

				$postObject = new Varien_Object();
				$postObject->setData($post);
				
				$vendorPostObject = new Varien_Object();
				$vendorPostObject->setData($vendorData);
				//echo "<pre>"; print_r($vendorPostObject); exit;
				
				/* Admin Email */
				$mailTemplate = Mage::getModel('core/email_template');
				
				$mailTemplate->addBcc(Mage::getStoreConfig(self::XML_PATH_EMAIL_BCC));
				
				/* @var $mailTemplate Mage_Core_Model_Email_Template */
				$mailTemplate->setDesignConfig(array('area' => 'frontend'))
							->setReplyTo($post['email'])
							->sendTransactional(
									Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE),
									Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER),
									Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT),
									null,
									array('data' => $postObject,'vendor'=>$vendorPostObject)
							);
				/* Admin Email Ends */
				
				/* User Email */
				$mailTemplate->setDesignConfig(array('area' => 'frontend'))
							->setReplyTo(Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT))
							->sendTransactional(
									Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE_USER),
									Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER),
									$post['email'],
									null,
									array('data' => $postObject)
							);
				/* User Email Ends */
				
				if (!$mailTemplate->getSentSuccess()) {
					throw new Exception();
				}
				
				$translate->setTranslateInline(true);
				//echo "sent";exit;
				
				//Mage::getSingleton('core/session')->addSuccess(Mage::helper('uploadtool')->__('Your inquiry was submitted and will be responded to as soon as possible. Thank you.'));
				$this->_redirectUrl($_POST['current_url']."?&msg=success");
				
				return;
			
			} catch (Exception $e) {
				$translate->setTranslateInline(true);
				
				//Mage::getSingleton('core/session')->addError(Mage::helper('uploadtool')->__('Unable to submit your request. Please, try again later'));
				$this->_redirectUrl($_POST['current_url']."?&msg=error");
				return;
			}
		
		} else {
	        $this->_redirectUrl($_POST['current_url']."?&msg=error");
	   	}
	}
}
?>
