<?php
class Dolphin_Modelfinder_IndexController extends Mage_Core_Controller_Front_Action{
    public function IndexAction() {
      
	  $this->loadLayout();   
	  $this->getLayout()->getBlock("head")->setTitle($this->__("Titlename"));
	        $breadcrumbs = $this->getLayout()->getBlock("breadcrumbs");
      $breadcrumbs->addCrumb("home", array(
                "label" => $this->__("Home Page"),
                "title" => $this->__("Home Page"),
                "link"  => Mage::getBaseUrl()
		   ));

      $breadcrumbs->addCrumb("titlename", array(
                "label" => $this->__("Titlename"),
                "title" => $this->__("Titlename")
		   ));

      $this->renderLayout(); 
	  
    }
    
    
	public function alllistAction()
	{
		$finder11 = Mage::app()->getRequest()->getParam(finder11);
		
		$finder11s =  explode(",",$finder11);
		
			
		
	}
	
	public function serieslistAction()
	{
		$id=(int)Mage::app()->getRequest()->getParam('id');
 		
		$category = Mage::getModel('catalog/category')->load($id);

		
		$products = Mage::getModel('catalog/product')
					->getCollection()
					->addAttributeToSelect('*')
					->addAttributeToFilter('printer_series', array('neq' => '' ))
					->addAttributeToFilter('type_id', array('eq' => 'grouped'))
					->addCategoryFilter($category);

  			
		//echo $products->getselect();
		
				
		foreach($products as $product)
		{
			$series[$product->getPrinterSeries()]=$product->getAttributeText('printer_series');
		}
		
		$series=array_unique($series);
		asort($series);
		//echo "<pre>";
		//print_r($series);
		$count=count($series);
		if($count > 0 )
		{
			$html="<option value='0'>Printer Series</option>";
			foreach($series as $key=>$list)
			{
				$html.="<option value=". $key .">".$list."</option>";
			}
		} 
		else
		{
			$html="<option value='0'>No Data Found</option>";
		}
		echo $html;
		
		
		
		
		
	}
	
	
	public function modellistAction()
	{
		
		$id=(int)Mage::app()->getRequest()->getParam('id');
 		$catid=(int)Mage::app()->getRequest()->getParam('cat');
 		
		$category = Mage::getModel('catalog/category')->load($catid);

		
		$products = Mage::getModel('catalog/product')
					->getCollection()
					->addAttributeToSelect('*')
 					->addAttributeToFilter('type_id', array('eq' => 'grouped'))
					->addCategoryFilter($category)
					->addAttributeToFilter('printer_series', array('finset' => $id ));

		
		foreach($products as $product)
		{
			$series[$product->getPrinterModel()]=$product->getAttributeText('printer_model');
		}
		asort($series);
				
		$html="<option value='0'>Printer Model</option>";
		foreach($series as $key=>$list)
		{
			$html.="<option value=". $key .">".$list."</option>";
		}
		
		echo $html; 
						
		
	}
	
	public function printerDataAction()
	{
		
		$modelid=(int)Mage::app()->getRequest()->getParam('id');
 		$catid=(int)Mage::app()->getRequest()->getParam('cat');
		$ser=(int)Mage::app()->getRequest()->getParam('ser');
		
	/*	Mage::getSingleton('core/session')->setBrandid($catid);
		Mage::getSingleton('core/session')->setSeriesid($ser);
		Mage::getSingleton('core/session')->setModelid($modelid); */
		
		
		$category = Mage::getModel('catalog/category')->load($catid);

		
		$products = Mage::getModel('catalog/product')
					->getCollection()
					->addAttributeToSelect('*')
 					->addAttributeToFilter('type_id', array('eq' => 'grouped'))
					->addCategoryFilter($category)
					->addAttributeToFilter('printer_series', array('finset' => $ser ))
					->addAttributeToFilter('printer_model', array('finset' => $modelid ));

		
		$html='';
		foreach($products as $product)
		{
			
			//$html='<a class="selectprinter" href="'.$product->getProductUrl().'">Select Your Printer</a>';
			$html=$product->getProductUrl();
		}
		
		
		
		echo $html; 
						
		
	}
	
	public function printerData2Action()
	{
		
		$modelid=(int)Mage::app()->getRequest()->getParam('id');
 		$catid=(int)Mage::app()->getRequest()->getParam('cat');
		$ser=(int)Mage::app()->getRequest()->getParam('ser');
		
		Mage::getSingleton('core/session')->setBrandid($catid);
		Mage::getSingleton('core/session')->setSeriesid($ser);
		Mage::getSingleton('core/session')->setModelid($modelid);
		
		
		$category = Mage::getModel('catalog/category')->load($catid);

		
		$products = Mage::getModel('catalog/product')
					->getCollection()
					->addAttributeToSelect('*')
 					->addAttributeToFilter('type_id', array('eq' => 'grouped'))
					->addCategoryFilter($category)
					->addAttributeToFilter('printer_series', array('finset' => $ser ))
					->addAttributeToFilter('printer_model', array('finset' => $modelid ));

		
		$html='';
		
		foreach($products as $product)
		{
			$productid = $product->getEntityId();
			$html='<a class="selectprinter" href="'.$product->getProductUrl().'">Set Your Printer</a>';
			
		}
		Mage::getSingleton('core/session')->setEntityid($productid); 
		
		
		echo $html; 
						
		
	}
	
	public function printerData1Action()
	{
		
		$modelid=(int)Mage::app()->getRequest()->getParam('id');
 		$catid=(int)Mage::app()->getRequest()->getParam('cat');
		$ser=(int)Mage::app()->getRequest()->getParam('ser');
		
		/* Mage::getSingleton('core/session')->setBrandid($catid);
		Mage::getSingleton('core/session')->setSeriesid($ser);
		Mage::getSingleton('core/session')->setModelid($modelid); */
		
		
		$category = Mage::getModel('catalog/category')->load($catid);

		
		$products = Mage::getModel('catalog/product')
					->getCollection()
					->addAttributeToSelect('*')
 					->addAttributeToFilter('type_id', array('eq' => 'grouped'))
					->addCategoryFilter($category)
					->addAttributeToFilter('printer_series', array('finset' => $ser ))
					->addAttributeToFilter('printer_model', array('finset' => $modelid ));

		
		$html='';
		foreach($products as $product)
		{
			
			$html='<a class="selectprinter" href="'.$product->getProductUrl().'">Find Now</a>';
		}
		
		
		
		echo $html; 
						
		
	}
	
	public function setmyprinterAction()
	{
		$modelid=(int)Mage::app()->getRequest()->getParam('model');
 		$catid=(int)Mage::app()->getRequest()->getParam('brand');
		$ser=(int)Mage::app()->getRequest()->getParam('series');
		
		$brandname=(string)Mage::app()->getRequest()->getParam('brandname');
		$modelname=(string)Mage::app()->getRequest()->getParam('modelname');
		
		
		Mage::getSingleton('core/session')->setBrandid($catid);
		Mage::getSingleton('core/session')->setSeriesid($ser); 
		Mage::getSingleton('core/session')->setModelid($modelid);
		
		
		
		$category = Mage::getModel('catalog/category')->load($catid);

		
		$products = Mage::getModel('catalog/product')
					->getCollection()
					->addAttributeToSelect('*')
 					->addAttributeToFilter('type_id', array('eq' => 'grouped'))
					->addCategoryFilter($category)
					->addAttributeToFilter('printer_series', array('finset' => $ser ))
					->addAttributeToFilter('printer_model', array('finset' => $modelid ));

		
		$html='';
		
		foreach($products as $product)
		{
			$productid = $product->getEntityId();
			$html='<a class="selectprinter" href="'.$product->getProductUrl().'">Set Your Printer</a>';
			
		}
		Mage::getSingleton('core/session')->setEntityid($productid); 
		
		
		$getData= array($productid);
		$productCollection = Mage::getModel('catalog/product')->getCollection()
					->addFieldToFilter('entity_id', array('in'=> $getData));
		$productCollection->load();
	
		
		//echo $currentUrl = Mage::helper('core/url')->getCurrentUrl();
		
		$url = Mage::helper('core/http')->getHttpReferer() ? Mage::helper('core/http')->getHttpReferer()  : Mage::getUrl();
Mage::app()->getResponse()->setRedirect($url);
		
		
		Mage::getSingleton('catalog/session')->addSuccess(Mage::helper('contacts')->__($modelname.' Added to Your Printers. Log in or Create an Account for easy re-ordering.'));
       echo $url;
		

		
	} 
	
	public function addtocartAction()
	{
		/*$_productId = $_REQUEST['cartid']; 
		$_product = Mage::getModel('catalog/product')->load($_productId);
		$_url = Mage::helper('checkout/cart')->getAddUrl($_product); */
		
		$product = $this->getRequest()->getParam('cartid');
        $key =  Mage::getSingleton('core/session')->getFormKey();;
        if (empty($product)){ //if no product id, redirect to homepage
            $this->_redirect(''); 
        }
        else{ //redirect to the add to cart with the form key
            $this->_redirect('checkout/cart/add', array('product'=>$product, 'form_key'=>$key));
        }
		
	}
	
	
	public function modellistmobileAction()
	{
		
		$id=(int)Mage::app()->getRequest()->getParam('id');
 		$catid=(int)Mage::app()->getRequest()->getParam('cat');
 		
		$category = Mage::getModel('catalog/category')->load($catid);

		
		$products = Mage::getModel('catalog/product')
					->getCollection()
					->addAttributeToSelect('*')
 					->addAttributeToFilter('type_id', array('eq' => 'grouped'))
					->addCategoryFilter($category)
					->addAttributeToFilter('printer_series', array('finset' => $id ));

		
		foreach($products as $product)
		{
			$series[$product->getPrinterModel()]=$product->getAttributeText('printer_model');
		}
		asort($series);
		$html="<option value='0'>Printer Model</option>";
		foreach($series as $key=>$list)
		{
			$html.="<option value=". $key .">".$list."</option>";
		}
		
		echo $html; 
						
		
	}

	
	
}
