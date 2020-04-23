<?php

namespace PaktDigital\FocusPointBundle\Imagine\Filter\Loader;

use Imagine\Filter\Basic\Crop;
use Imagine\Filter\Basic\Resize;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\Point;
use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;

class FocusPointFilterLoader implements LoaderInterface
{
    private const WIDTH = 'width';

    private const HEIGHT = 'height';

    /**
     * @return ImageInterface
     */
    public function load(ImageInterface $image, array $options = [])
    {
        $x = isset($options['x']) ? (float) $options['x'] : 0;
        $y = isset($options['y']) ? (float) $options['y'] : 0;

        $cropWidth = isset($options['size'][0]) ? $options['size'][0] : null;
        $cropHeight = isset($options['size'][1]) ? $options['size'][1] : null;

        $size = $image->getSize();
        $origWidth = $size->getWidth();
        $origHeight = $size->getHeight();

        $origRatio = $this->ratio($origWidth, $origHeight);
        $newRatio = $this->ratio($cropWidth, $cropHeight);

        $fit = ($newRatio < $origRatio) ? self::HEIGHT : self::WIDTH;

        if (self::WIDTH === $fit) {
            $width = $cropWidth;
            $height = $cropWidth / $origRatio;
        }

        if (self::HEIGHT === $fit) {
            $width = $cropHeight * $origRatio;
            $height = $cropHeight;
        }

        $x = $width * (1 + $x) / 2;
        $y = $height - $height * (1 + $y) / 2;

        if (self::WIDTH === $fit) {
            $offsetX = 0;
            $offsetY = min($height - $cropHeight, max(0, $y - $cropHeight / 2));
        }

        if (self::HEIGHT === $fit) {
            $offsetX = min($width - $cropWidth, max(0, $x - $cropWidth / 2));
            $offsetY = 0;
        }

        $resize = new Resize(new Box($width, $height));
        $crop = new Crop(
            new Point($offsetX, $offsetY),
            new Box($cropWidth, $cropHeight)
        );

        $image = $crop->apply($resize->apply($image));

        return $image;
    }

    private function ratio(int $width, int $height): float
    {
        if ($height > 0) {
            return $width / $height;
        }

        return 1.0;
    }
}
