<?php

namespace Drupal\comment_overwrite;

use Drupal\comment\CommentLinkBuilder as Base;
use Drupal\Core\Entity\FieldableEntityInterface;

class CommentLinkBuilder extends Base{
  public function buildCommentedEntityLinks(FieldableEntityInterface $entity, array &$context) {
    return [];
  }
}