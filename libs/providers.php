<?php
require __DIR__ . '/posttagger.php';
/**
 * Description of providers
 *
 * @author Canaan
 */
class Providers {
	const URLS_TYPE_LEAD = 'lead';
	const URLS_TYPE_BLOCKED = 'blocked';
	const URLS_TYPE_INDEXED = 'indexed';
	const URLS_TYPE_ROBOTS_NOT_ALLOWED = 'robots_not_allowed';
	const URLS_TYPE_ERROR_NO_DATA = 'error_no_data';

	/**
	 * Get host by url
	 * @param string $url
	 * @param boolean $force get from database
	 * @return array
	 */
	public static function get_host_by_url($url, $force = false) {
		$cache_id = implode('-', db::create_host_params_from_url($url));
		if (!CacheManager::getInstance('hosts')->get($cache_id) || $force) {
			CacheManager::getInstance('hosts')
				->set($cache_id, db::get_host_by_url($url));
		}

		return CacheManager::getInstance('hosts')->get($cache_id);
	}
	/**
	 * Get host by url
	 * @param string $url
	 * @param boolean $force get from database
	 * @return array
	 */
	public static function get_url_by_url($url, $force = false) {
		$host = self::get_or_create_host_by_url($url);

		if (!$host || !isset($host['id']) || $host['id'] == 0) {
			return false;
		}

		$host_id = intval($host['id']);

		$cache_id = $host_id . '-' . implode('-', db::create_url_params_from_url($url));

		if (!CacheManager::getInstance('urls')->get($cache_id) || $force) {
			CacheManager::getInstance('urls')
				->set($cache_id, db::get_url_by_url($host_id, $url));
		}

		return CacheManager::getInstance('urls')->get($cache_id);
	}
	/**
	 * Get or Create Host item by Host Url string
	 * @param string $url
	 * @return array|boolean
	 */
	public static function get_or_create_host_by_url($url) {
		$host = self::get_host_by_url($url);
		if ($host) {
			return $host;
		}

		db::create_host($url);
		$host = self::get_host_by_url($url, true);

		if ($host) {
			return $host;
		}

		return false;
	}
	/**
	 * Get or Create url item by url string
	 * @param string $url
	 * @return array|boolean
	 */
	public static function get_or_create_url_by_url($url, $auto_inserted = false) {
		if (self::get_url_by_url($url)) {
			return self::get_url_by_url($url);
		}

		$host = self::get_or_create_host_by_url($url);
		db::create_url($host['id'], $url, $auto_inserted);

		$ourl = self::get_url_by_url($url, true);

		if ($ourl) {
			return $ourl;
		}

		return false;
	}

	/**
	 * Getting lead url
	 * @return string
	 */
	public static function get_lead_urls() {
		return db::get_lead_urls();
	}

	/**
	 * Check is url is on black list
	 * @param string $url
	 * @return boolean
	 */
	public static function isUrlAllowed($url) {
		//@TODO: You can do what ever you want
		return true;
	}
	/**
	 * @see const URLS_TYPE_*
	 * @param string $url
	 * @param mixed $status
	 */
	public static function change_url_status($url, $status) {
		$u = self::get_url_by_url($url);

		if (!$u) {
			return false;
		}

		db::update_url_status($u['id'], $status);
		return self::get_url_by_url($url, true);
	}
	/**
	 *
	 * @param array $urls
	 */
	public static function insert_url_list($urls, $auto_inserted = true) {
		foreach ($urls as $url => $desc) {
			// Actualy i dont care about "$desc" :)
			if (self::get_url_by_url($url)) {
				continue;
			}

			if (Providers::isUrlAllowed($url)) {
				self::get_or_create_url_by_url($url, $auto_inserted);
			}

		}
	}
	/**
	 * Creating search item data
	 * @param ContentAnalyzer $ca
	 * @return type
	 */
	public static function create_search_item(ContentAnalyzer $ca) {

		$desc = '';
		$meta = $ca->getMetaTags();
        $title = $ca->getTitle();
		$url = $ca->getUrl();

		if ( $title == '')
			$title = $ca->getOgTitle();

		$content = $ca->getPlainContent();
		if ( $content == '')
			$content = $ca->getOgDescription();

        if ($title == '')
            return false;

		if (isset($meta['description'])) {
			$desc = (is_array($meta['description'])) ? implode(' ', $meta['description']) : $meta['description'];
		}

		$host = parse_url($url)['host'];
		$keywords = self::create_keywords_from_content($content.' '.$title, $url);
		if (isset($meta['keywords']) && !empty($meta['keywords'])) {
			$keywords = (is_array($meta['keywords'])) ? implode(' ', $meta['keywords']) : $meta['keywords'];
		}

		if ($content == '' && $desc != '') {
			$content = $desc;
		}

		if ( str_word_count($content) < 5 )
			return false;

		if ( $content == "" )
			return false;

        if (strlen($content) != strlen(utf8_decode($content)))
            return false;


		return db::create_fulltext_item($title, $content, $keywords, $url, $desc);
	}

	/**
	 * create keywords from content of page
	 * @param $content plain text content from page
	 * @return string
	 */
	public function create_keywords_from_content($content, $url) {
		$host = parse_url($url)['host'];
		$tagger = new PosTagger(__DIR__ . "/lexicon.txt");
		$tags = $tagger->tag($content);
		return self::printTag($tags, $host);
	}

	/**
	 *
	 * @param $tags
	 * @return string
	 */
	public function printTag($tags, $url) {
		$texts = $url . ' ' . explode('.', $url)[1] . ' ';
		foreach ($tags as $t) {
			$tag = strtolower(trim($t['tag']));

			if (
				$tag == strtolower("NP") ||
				$tag == strtolower("NPS") ||
				$tag == strtolower("NN") ||
				$tag == strtolower("NNP") ||
				$tag == strtolower("NNPS") ||
				$tag == strtolower("NNS") ||
				$tag == strtolower("VBP") ||
				$tag == strtolower("FW") ||
				$tag == strtolower("RB") ||
				$tag == strtolower("RBD") ||
				$tag == strtolower("JJ") ||
				$tag == strtolower("JJSS") ||
				$tag == strtolower("RBR") ||
				$tag == strtolower("UH") ||
				$tag == strtolower("VB") ||
				$tag == strtolower("VBN") ||
				$tag == strtolower("VBD") ||
				$tag == strtolower("VBG") ||
				$tag == strtolower("VBZ") ||
				$tag == strtolower("JJR") ||
				$tag == strtolower("JJS")
			) {

				$texts .= $t['token'] . " ";
			}
		}
		return $texts;
	}
}

?>
