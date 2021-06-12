<?php
	/**
	 * iarecoding page.
	 *
	 * Handle functionality for iarecoding page.
	 *
	 * @package		iarecoding/app
	 * @subpackage	controllers
	 * @author		Justin Stolpe
	 * @link		https://github.com/jstolpe/iarecoding
	 * @version     1.0.0
	 */
	class IAreCoding extends Controller {
		/**
		 * Index function.
		 *
		 * Load the home view.
		 *
		 * @return void
		 */
		public function index() {
			// html page title
			$data['html_title'] = 'iarecoding';

			// get data from model
			$data['page'] = $this->IAreCodingModel->getPageText();

			// get the description html snippet
			$data['description_html'] = $this->Model->getViewHtml( 'iarecoding/snippet_html_description', $data );

			// load view
			$this->loadView( 'iarecoding/html_template', $data );
		}
	}
?>