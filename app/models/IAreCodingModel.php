<?php
	/**
	 * iarecoding model.
	 *
	 * Get data for the iarecoding page.
	 *
	 * @package		iarecoding/app
	 * @subpackage	models
	 * @author		Justin Stolpe
	 * @link		https://github.com/jstolpe/iarecoding
	 * @version     1.0.0
	 */
	class IAreCodingModel extends Model {
		/**
		 * Class constructor.
		 *
		 * Do things upon construction of the model.
		 *
		 * @param array $autoloader Instance of the autoloader class.
		 * @return void
		 */
		public function __construct( $autoloader ) {
			parent::__construct( $autoloader );

			// make sure things are loaded for use in the model
			$this->loadAllModels( $autoloader, __CLASS__ );
		}

		/**
		 * Get iarecoding page text.
		 *
		 * @return array for display on the page
		 */
		public function getPageText() {
			// get page text from database or code
			return USE_DATABASE ? $this->getPageTextFromDatabase() : $this->getPageTextFromCode();
		}

		/**
		 * Get iarecoding page text from the code.
		 *
		 * @return array for display on the page
		 */
		public function getPageTextFromCode() {
			return array( // page info
				'info' => array(
					'title' => 'iarecoding',
					'subtitle' => 'a simple PHP MVC framework.',
					'description' => 'Learn to code this MVC (Model-View-Controller) framework from scratch! The YouTube/GitHub links below will walk you through the process of coding the iarecoding framework so you learn and have a better understanding of how frameworks work :D',
				),
				'links' => array(
					array(
						'link_title' => 'GitHub',
						'link_url' => 'https://github.com/jstolpe/iarecoding'
					),
					array(
						'link_title' => 'YouTube',
						'link_url' => 'https://youtube.com/justinstolpe'
					)
				)
			);
		}

		/**
		 * Get iarecoding page text from the database.
		 *
		 * @return array $page for display on the page
		 */
		public function getPageTextFromDatabase() {
			// select table columns
			$select = '
				templates.*,
				template_links.title as link_title,
				template_links.url as link_url
			';

			// specify database table
			$this->database->table( 'templates' );

			// join template links
			$this->database->join( 'left', 'template_links', 'template_links.template_id = templates.id' );

			// set where email equals the email passed in
			$this->database->where( 'templates.id', '1' );

			// set where email equals the email passed in
			$this->database->orderBy( 'template_links.title', 'ASC' );

			// set fetch mode
			$this->database->fetch( Database::PDO_FETCH_MULTI );

			// return results
			$templateLinks = $this->database->runSelectQuery( $select );

			$page = array( // structure for our return data
				'info' => array(),
				'links' => array()
			);

			foreach ( $templateLinks as $link ) { // loop over links
				// store template info
				$page['info'] = $link;

				// store link
				$page['links'][] = $link;
			}

			// return the data
			return $page;
		}
	}
?>