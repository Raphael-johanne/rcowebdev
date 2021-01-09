<?php

namespace Drupal\ratp_schedule\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Database\Connection;
/**
 * Bus controller.
 */
class BusController extends ControllerBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Construct
   *
   * @param \Drupal\Core\Database\Connection $databaseConnection
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * 
   */
  public function schedule($id) {

    $schedule = $this->getSchedule($id);

    if (!$schedule) {
      throw new \Exception($this->t('Schedule does not exist'));
    }

    $result = [];
    $date   = new \DateTime();
    $url    = sprintf('https://www.ratp.fr/horaires-bus?network-current=busratp&networks=busratp&line_busratp=%d&name_line_busratp=%s+%%2F+%s&id_line_busratp=%s&id_t_line_busratp=&line_noctilien=&name_line_noctilien=&id_line_noctilien=&id_t_line_noctilien=&stop_point_rer=&stop_point_metro=&stop_point_busratp=%s&stop_point_tram=&stop_point_noctilien=&type=now&departure_date=%s&departure_hour=%d&departure_minute=15&op=Rechercher&is_mobile=&form_build_id=form-iSeMItNHJs7KxIiKGQSha1o177rC5uJwitBQRRz06Sc&form_id=scheduledform'
      , $schedule->ratp_schedule_number
      , urlencode($schedule->ratp_schedule_terminus_1)
      , urlencode($schedule->ratp_schedule_terminus_2)
      , $schedule->ratp_schedule_type . $schedule->ratp_schedule_number // Example B46,
      , str_replace(' ', '+', $schedule->ratp_schedule_station_departure)
      , str_replace('+', '%2F', urlencode($date->format('d m Y'))) // current departure date
      , $date->format('H') // current departure hour
    );

    //var_dump($url);

    $context = file_get_contents($url);
    preg_match_all('#<li class="body-busratp">(.*)</li>#Us', $context, $matches);

    if (isset($matches[1]) && !empty($matches[1])) {
      foreach ($matches[1] as $match) {
        $match = preg_replace("#\n|\t|\r#","", $match);
        preg_match('#<span class="[a-z-]+">([a-zA-Z ]+)</span>[ ]+<span class="[a-z-]+">([a-zA-Z0-9 ]+)</span>#', $match, $info);
        
        $result['schedule'] = ['name' => $schedule->ratp_schedule_name]; 
        $result['schedules'][] = [
          'station' => $info[1],
          'time'    => $info[2]
        ];
      }
    }

    $response = new Response();
    $response->headers->set('Content-Type', 'text/json; charset=UTF8');
    $response->setContent(json_encode($result));
   
    return $response;
  }

  /**
   * Get schedule
   */
  protected function getSchedule($id) {
    return $this->connection->select('ratp_schedule')
            ->fields('ratp_schedule', [
              'ratp_schedule_name', 
              'ratp_schedule_type', 
              'ratp_schedule_number', 
              'ratp_schedule_terminus_1',
              'ratp_schedule_terminus_2',
              'ratp_schedule_station_departure'
            ])
            ->condition('ratp_schedule_id', $id, "=")
            ->execute()
            ->fetchAll()[0];
  }
}
