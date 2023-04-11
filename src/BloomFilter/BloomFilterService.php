<?php

namespace AsyncCenter\BloomFilter;

class BloomFilterService
{

    const TYPE_BUCKET_VIDEO_URL = 'video_url';               //过滤类型  视频地址URL

    public function test()
    {
        //布隆过滤器：用于判断是否重复    默认存在 redis database =  3
        $bloomFilter = new BloomFilter(self::TYPE_BUCKET_VIDEO_URL);

        $video = 'https://media.w3.org/2010/05/sintel/trailer.mp4';
        $videoUrls = [
            'https://media.w3.org/2010/05/sintel/trailer.mp4',
            'http://www.w3school.com.cn/example/html5/mov_bbb.mp4',
            'https://www.w3schools.com/html/movie.mp4',
            'http://clips.vorwaerts-gmbh.de/big_buck_bunny.mp4',
            'http://devimages.apple.com/iphone/samples/bipbop/bipbopall.m3u8',
        ];

        $bloomFilter->add($video);                          //单个添加
        var_dump($bloomFilter->exists($video));             //单个校验

        $bloomFilter->multiAdd($videoUrls);
        var_dump($bloomFilter->multiExists($videoUrls));     //批量校验
    }

}

