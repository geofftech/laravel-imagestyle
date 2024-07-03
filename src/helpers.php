<?php
use GeoffTech\LaravelImageStyle\ImageStyle;

function imagestyle(string $path)
{
  return new ImageStyle($path);
}