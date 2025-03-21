<?php

namespace Core;

class Validator {
  public static function string(string $value, float $min = 1, float $max = INF) {
    $value = trim($value);
    return mb_strlen($value) >= $min && mb_strlen($value) <= $max;
  }
}