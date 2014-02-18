<?php

/*
  RSS Feed Generator for PHP 4 or higher version
  Written by Vagharshak Tozalakyan <vagh@armdex.com>
  License: GNU Public License

  Classes in package:
    class rssGenerator_rss
    class rssGenerator_channel
    class rssGenerator_image
    class rssGenerator_textInput
    class rssGenerator_item

  For additional information please reffer the documentation
*/

class rssGenerator_rss
{

  var $rss_version = '2.0';
  var $encoding = '';

  function createFeed($channel)
  {
    $rss = '<?xml version="1.0"';
    if (!empty($this->encoding))
    {
      $rss .= ' encoding="' . $this->encoding . '"';
    }
    $rss .= '?>' . "\n";
    $rss .= '<!-- Generated on ' . date('r') . ' -->' . "\n";
    $rss .= '<rss version="' . $this->rss_version . '">' . "\n";
    $rss .= '  <channel>' . "\n";
    $rss .= '    <title>' . $channel->title . '</title>' . "\n";
    $rss .= '    <link>' . $channel->link . '</link>' . "\n";
    $rss .= '    <description>' . $channel->description . '</description>' . "\n";
    if (!empty($channel->language))
    {
      $rss .= '    <language>' . $channel->language . '</language>' . "\n";
    }
    if (!empty($channel->copyright))
    {
      $rss .= '    <copyright>' . $channel->copyright . '</copyright>' . "\n";
    }
    if (!empty($channel->managingEditor))
    {
      $rss .= '    <managingEditor>' . $channel->managingEditor . '</managingEditor>' . "\n";
    }
    if (!empty($channel->webMaster))
    {
      $rss .= '    <webMaster>' . $channel->webMaster . '</webMaster>' . "\n";
    }
    if (!empty($channel->pubDate))
    {
      $rss .= '    <pubDate>' . $channel->pubDate . '</pubDate>' . "\n";
    }
    if (!empty($channel->lastBuildDate))
    {
      $rss .= '    <lastBuildDate>' . $channel->lastBuildDate . '</lastBuildDate>' . "\n";
    }
    foreach ($channel->categories as $category)
    {
      $rss .= '    <category';
      if (!empty($category['domain']))
      {
        $rss .= ' domain="' . $category['domain'] . '"';
      }
      $rss .= '>' . $category['name'] . '</category>' . "\n";
    }
    if (!empty($channel->generator))
    {
      $rss .= '    <generator>' . $channel->generator . '</generator>' . "\n";
    }
    if (!empty($channel->docs))
    {
      $rss .= '    <docs>' . $channel->docs . '</docs>' . "\n";
    }
    if (!empty($channel->ttl))
    {
      $rss .= '    <ttl>' . $channel->ttl . '</ttl>' . "\n";
    }
    if (sizeof($channel->skipHours))
    {
      $rss .= '    <skipHours>' . "\n";
      foreach ($channel->skipHours as $hour)
      {
        $rss .= '      <hour>' . $hour . '</hour>' . "\n";
      }
      $rss .= '    </skipHours>' . "\n";
    }
    if (sizeof($channel->skipDays))
    {
      $rss .= '    <skipDays>' . "\n";
      foreach ($channel->skipDays as $day)
      {
        $rss .= '      <day>' . $day . '</day>' . "\n";
      }
      $rss .= '    </skipDays>' . "\n";
    }
    if (!empty($channel->image))
    {
      $image = $channel->image;
      $rss .= '    <image>' . "\n";
      $rss .= '      <url>' . $image->url . '</url>' . "\n";
      $rss .= '      <title>' . $image->title . '</title>' . "\n";
      $rss .= '      <link>' . $image->link . '</link>' . "\n";
      if (image.width)
      {
        $rss .= '      <width>' . $image->width . '</width>' . "\n";
      }
      if ($image.height)
      {
        $rss .= '      <height>' . $image->height . '</height>' . "\n";
      }
      if (!empty($image->description))
      {
        $rss .= '      <description>' . $image->description . '</description>' . "\n";
      }
      $rss .= '    </image>' . "\n";
    }
    if (!empty($channel->textInput))
    {
      $textInput = $channel->textInput;
      $rss .= '    <textInput>' . "\n";
      $rss .= '      <title>' . $textInput->title . '</title>' . "\n";
      $rss .= '      <description>' . $textInput->description . '</description>' . "\n";
      $rss .= '      <name>' . $textInput->name . '</name>' . "\n";
      $rss .= '      <link>' . $textInput->link . '</link>' . "\n";
      $rss .= '    </textInput>' . "\n";
    }
    if (!empty($channel->cloud_domain) || !empty($channel->cloud_path) ||
      !empty($channel->cloud_registerProcedure) || !empty($channel->cloud_protocol))
    {
      $rss .= '    <cloud domain="' . $channel->cloud_domain . '" ';
      $rss .= 'port="' . $channel->cloud_port . '" path="' . $channel->cloud_path . '" ';
      $rss .= 'registerProcedure="' . $channel->cloud_registerProcedure . '" ';
      $rss .= 'protocol="' . $channel->cloud_protocol . '" />' . "\n";
    }
    if (!empty($channel->extraXML))
    {
      $rss .= $channel->extraXML . "\n";
    }
    foreach ($channel->items as $item)
    {
      $rss .= '    <item>' . "\n";
      if (!empty($item->title))
      {
        $rss .= '      <title>' . $item->title . '</title>' . "\n";
      }
      if (!empty($item->description))
      {
        $rss .= '      <description>' . $item->description . '</description>' . "\n";
      }
      if (!empty($item->link))
      {
        $rss .= '      <link>' . $item->link . '</link>' . "\n";
      }
      if (!empty($item->pubDate))
      {
        $rss .= '      <pubDate>' . $item->pubDate . '</pubDate>' . "\n";
      }
      if (!empty($item->author))
      {
        $rss .= '      <author>' . $item->author . '</author>' . "\n";
      }
      if (!empty($item->comments))
      {
        $rss .= '      <comments>' . $item->comments . '</comments>' . "\n";
      }
      if (!empty($item->guid))
      {
        $rss .= '      <guid isPermaLink="';
        $rss .= ($item->guid_isPermaLink ? 'true' : 'false') . '">';
        $rss .= $item->guid . '</guid>' . "\n";
      }
      if (!empty($item->source))
      {
        $rss .= '      <source url="' . $item->source_url . '">';
        $rss .= $item->source . '</source>' . "\n";
      }
      if (!empty($item->enclosure_url) || !empty($item->enclosure_type))
      {
        $rss .= '      <enclosure url="' . $item->enclosure_url . '" ';
        $rss .= 'length="' . $item->enclosure_length . '" ';
        $rss .= 'type="' . $item->enclosure_type . '" />' . "\n";
      }
      foreach ($item->categories as $category)
      {
        $rss .= '      <category';
        if (!empty($category['domain']))
        {
          $rss .= ' domain="' . $category['domain'] . '"';
        }
        $rss .= '>' . $category['name'] . '</category>' . "\n";
      }
      $rss .= '    </item>' . "\n";
    }
    $rss .= '  </channel>' . "\n";
    return $rss .= '</rss>';
  }

}

class rssGenerator_channel
{

  var $title = '';
  var $link = '';
  var $description = '';
  var $language = '';
  var $copyright = '';
  var $managingEditor = '';
  var $webMaster = '';
  var $pubDate = '';
  var $lastBuildDate = '';
  var $categories = array();
  var $generator = '';
  var $docs = '';
  var $ttl = '';
  var $image = '';
  var $textInput = '';
  var $skipHours = array();
  var $skipDays = array();
  var $cloud_domain = '';
  var $cloud_port = '80';
  var $cloud_path = '';
  var $cloud_registerProcedure = '';
  var $cloud_protocol = '';
  var $items = array();
  var $extraXML = '';

}

class rssGenerator_image
{

  var $url = '';
  var $title = '';
  var $link = '';
  var $width = '88';
  var $height = '31';
  var $description = '';

}

class rssGenerator_textInput
{

  var $title = '';
  var $description = '';
  var $name = '';
  var $link = '';

}

class rssGenerator_item
{

  var $title = '';
  var $description = '';
  var $link = '';
  var $author = '';
  var $pubDate = '';
  var $comments = '';
  var $guid = '';
  var $guid_isPermaLink = true;
  var $source = '';
  var $source_url = '';
  var $enclosure_url = '';
  var $enclosure_length = '0';
  var $enclosure_type = '';
  var $categories = array();

}

?>