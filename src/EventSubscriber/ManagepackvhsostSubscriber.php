<?php

namespace Drupal\managepackvhsost\EventSubscriber;

use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * managepackvhsost event subscriber.
 */
class ManagepackvhsostSubscriber implements EventSubscriberInterface {
  
  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;
  
  /**
   * Constructs event subscriber.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *        The messenger.
   */
  public function __construct(MessengerInterface $messenger) {
    $this->messenger = $messenger;
  }
  
  /**
   * Kernel request event handler.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *        Response event.
   */
  public function onKernelRequest(GetResponseEvent $event) {
    // $this->messenger->addStatus(__FUNCTION__);
  }
  
  /**
   * Kernel response event handler.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *        Response event.
   */
  public function onKernelResponse(FilterResponseEvent $event) {
    // $this->messenger->addStatus(__FUNCTION__);
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    // return [
    // KernelEvents::REQUEST => [
    // 'onKernelRequest'
    // ],
    // KernelEvents::RESPONSE => [
    // 'onKernelResponse'
    // ]
    // ];
    $events[KernelEvents::RESPONSE][] = [
      'RemoveXFrameOptions',
      -10
    ];
    return $events;
  }
  
  public function RemoveXFrameOptions(ResponseEvent $event) {
    $response = $event->getResponse();
    $response->headers->remove('X-Frame-Options');
  }
  
}
