<?php

namespace Drupal\managepackvhsost\Services;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Component\Serialization\Json;
use Stephane888\Debug\Repositories\ConfigDrupal;

/**
 *
 * @author stephane
 *        
 */
class CheckDomains {
  private $apiJey = null;
  protected $http_code;
  
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  
  /**
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $user;
  
  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;
  
  /**
   *
   * @param EntityTypeManagerInterface $entity_type_manager
   * @param AccountProxy $user
   * @param MessengerInterface $messenger
   */
  function __construct(EntityTypeManagerInterface $entity_type_manager, AccountProxy $user, MessengerInterface $messenger) {
    $this->entityTypeManager = $entity_type_manager;
    $this->user = $user;
    $this->messenger = $messenger;
  }
  
  /**
   * Dans cette appproche on utilisa l'api : api.apilayer.com, or OVH permet
   * aussi de verifier la disponilité du domaine.
   *
   * @param string $search
   * @return boolean[]|mixed[]
   */
  public function searchOld($search) {
    $search = strtolower($search);
    $result = [
      'status_domain' => false
    ];
    $result['status'] = $this->is_domain($search);
    if ($result['status']) {
      $api_result = $this->curlApi($search);
      if ($this->http_code == 200) {
        $api_result = Json::decode($api_result);
        if (!empty($api_result['result']) && $api_result['result'] == 'available') {
          $result['status_domain'] = true;
        }
        $result['api_result'] = $api_result;
      }
      $result['code'] = $this->http_code;
    }
    return $result;
  }
  
  public function search($search) {
    $search = strtolower($search);
    $result = [
      'status_domain' => false
    ];
    $result['status'] = $this->is_domain($search);
    if ($result['status']) {
      //
    }
    return $result;
  }
  
  private function is_valid_domain_name($domain_name) {
    return (preg_match("/^([a-zd](-*[a-zd])*)(.([a-zd](-*[a-zd])*))*$/i", $domain_name) && // valid
                                                                                              // characters
                                                                                              // check
    preg_match("/^.{1,253}$/", $domain_name) && // overall length check
    preg_match("/^[^.]{1,63}(.[^.]{1,63})*$/", $domain_name)); // length of
                                                               // every label
  }
  
  protected function domain_name($domain_name) {
    // FILTER_VALIDATE_URL checks length but..why not? so we dont move forward
    // with more expensive operations
    $domain_len = strlen($domain_name);
    if ($domain_len < 3 or $domain_len > 253)
      return FALSE;
    
    // getting rid of HTTP/S just in case was passed.
    if (stripos($domain_name, 'http://') === 0)
      $domain_name = substr($domain_name, 7);
    elseif (stripos($domain_name, 'https://') === 0)
      $domain_name = substr($domain_name, 8);
    
    // we dont need the www either
    if (stripos($domain_name, 'www.') === 0)
      $domain_name = substr($domain_name, 4);
    
    // Checking for a '.' at least, not in the beginning nor end, since
    // http://.abcd. is reported valid
    if (strpos($domain_name, '.') === FALSE or $domain_name[strlen($domain_name) - 1] == '.' or $domain_name[0] == '.')
      return FALSE;
    
    // now we use the FILTER_VALIDATE_URL, concatenating http so we can use it,
    // and return BOOL
    return (filter_var('http://' . $domain_name, FILTER_VALIDATE_URL) === FALSE) ? FALSE : TRUE;
  }
  
  function is_domain($d, $clean = false) {
    if ($clean === true)
      $d = $this->cleanURL($d);
    $tlds = "/^[-a-z0-9]{1,63}\.(ac\.nz|co\.nz|geek\.nz|gen\.nz|kiwi\.nz|maori\.nz|net\.nz|org\.nz|school\.nz|ae|ae\.org|com\.af|asia|asn\.au|auz\.info|auz\.net|com\.au|id\.au|net\.au|org\.au|auz\.biz|az|com\.az|int\.az|net\.az|org\.az|pp\.az|biz\.fj|com\.fj|info\.fj|name\.fj|net\.fj|org\.fj|pro\.fj|or\.id|biz\.id|co\.id|my\.id|web\.id|biz\.ki|com\.ki|info\.ki|ki|mobi\.ki|net\.ki|org\.ki|phone\.ki|biz\.pk|com\.pk|net\.pk|org\.pk|pk|web\.pk|cc|cn|com\.cn|net\.cn|org\.cn|co\.in|firm\.in|gen\.in|in|in\.net|ind\.in|net\.in|org\.in|co\.ir|ir|co\.jp|jp|jp\.net|ne\.jp|or\.jp|co\.kr|kr|ne\.kr|or\.kr|co\.th|in\.th|com\.bd|com\.hk|hk|idv\.hk|org\.hk|com\.jo|jo|com\.kz|kz|org\.kz|com\.lk|lk|org\.lk|com\.my|my|com\.nf|info\.nf|net\.nf|nf|web\.nf|com\.ph|ph|com\.ps|net\.ps|org\.ps|ps|com\.sa|com\.sb|net\.sb|org\.sb|com\.sg|edu\.sg|org\.sg|per\.sg|sg|com\.tw|tw|com\.vn|net\.vn|org\.vn|vn|cx|fm|io|la|mn|nu|qa|tk|tl|tm|to|tv|ws|academy|careers|education|training|bike|biz|cat|co|com|info|me|mobi|name|net|org|pro|tel|travel|xxx|blackfriday|clothing|diamonds|shoes|tattoo|voyage|build|builders|construction|contractors|equipment|glass|lighting|plumbing|repair|solutions|buzz|sexy|singles|support|cab|limo|camera|camp|gallery|graphics|guitars|hiphop|photo|photography|photos|pics|center|florist|institute|christmas|coffee|kitchen|menu|recipes|company|enterprises|holdings|management|ventures|computer|systems|technology|directory|guru|tips|wiki|domains|link|estate|international|land|onl|pw|today|ac\.im|co\.im|com\.im|im|ltd\.co\.im|net\.im|org\.im|plc\.co\.im|am|at|co\.at|or\.at|ba|be|bg|biz\.pl|com\.pl|info\.pl|net\.pl|org\.pl|pl|biz\.tr|com\.tr|info\.tr|tv\.tr|web\.tr|by|ch|co\.ee|ee|co\.gg|gg|co\.gl|com\.gl|co\.hu|hu|co\.il|org\.il|co\.je|je|co\.nl|nl|co\.no|no|co\.rs|in\.rs|rs|co\.uk|org\.uk|uk\.net|com\.de|de|com\.es|es|nom\.es|org\.es|com\.gr|gr|com\.hr|com\.mk|mk|com\.mt|net\.mt|org\.mt|com\.pt|pt|com\.ro|ro|com\.ru|net\.ru|ru|su|com\.ua|ua|cz|dk|eu|fi|fr|pm|re|tf|wf|yt|gb\.net|ie|is|it|li|lt|lu|lv|md|mp|se|se\.net|si|sk|ac|ag|co\.ag|com\.ag|net\.ag|nom\.ag|org\.ag|ai|com\.ai|com\.ar|as|biz\.pr|com\.pr|net\.pr|org\.pr|pr|biz\.tt|co\.tt|com\.tt|tt|bo|com\.bo|com\.br|net\.br|tv\.br|bs|com\.bs|bz|co\.bz|com\.bz|net\.bz|org\.bz|ca|cl|co\.cr|cr|co\.dm|dm|co\.gy|com\.gy|gy|co\.lc|com\.lc|lc|co\.ms|com\.ms|ms|org\.ms|co\.ni|com\.ni|co\.ve|com\.ve|co\.vi|com\.vi|com\.co|net\.co|nom\.co|com\.cu|cu|com\.do|do|com\.ec|ec|info\.ec|net\.ec|com\.gt|gt|com\.hn|hn|com\.ht|ht|net\.ht|org\.ht|com\.jm|com\.kn|kn|com\.mx|mx|com\.pa|com\.pe|pe|com\.py|com\.sv|com\.uy|uy|com\.vc|net\.vc|org\.vc|vc|gd|gs|north\.am|south\.am|us|us\.org|sx|tc|vg|cd|cg|cm|co\.cm|com\.cm|net\.cm|co\.ke|or\.ke|co\.mg|com\.mg|mg|net\.mg|org\.mg|co\.mw|com\.mw|coop\.mw|mw|co\.na|com\.na|na|org\.na|co\.ug|ug|co\.za|com\.ly|ly|com\.ng|ng|com\.sc|sc|mu|rw|sh|so|st|club|kiwi|uno|email|ruhr)$/i";
    if (preg_match($tlds, $d))
      return true;
    else
      return false;
  }
  
  protected function cleanURL($url) {
    $url = preg_replace("/(^(http(s)?:\/\/|www\.))?(www\.)?([a-z-\.0-9]+)/", "$5", trim($url));
    if (preg_match("/^([a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,6})/", $url, $domain)) {
      return $domain[1];
    }
    else
      return "not valid domain or subdomain" . $url;
  }
  
  protected function getApiKey() {
    if (!$this->apiJey) {
      $conf = ConfigDrupal::config('managepackvhsost.settings');
      if (!empty($conf['api_apilayer_whois_api'])) {
        $this->apiJey = $conf['api_apilayer_whois_api'];
      }
      else {
        \Drupal::messenger()->addWarning('parametre de configuration non definit : api_apilayer_whois_api');
      }
    }
  }
  
  /**
   *
   * @param string $search
   * @return mixed
   */
  protected function curlApi(string $search) {
    $this->getApiKey();
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://api.apilayer.com/whois/check?domain=" . $search,
      CURLOPT_HTTPHEADER => array(
        "Content-Type: text/plain",
        "apikey: $this->apiJey"
      ),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET"
    ));
    $response = curl_exec($curl);
    $this->http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    return $response;
  }
  
  function gethttpCode() {
    return $this->http_code;
  }
  
}