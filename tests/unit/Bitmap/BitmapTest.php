<?php

declare(strict_types=1);

namespace Smoren\Schemator\Tests\Unit\Bitmap;

use Smoren\Schemator\Structs\Bitmap;

class BitmapTest extends \Codeception\Test\Unit
{
    public function testCreate()
    {
        $this->assertEquals(132, Bitmap::createFromArray([2, 7])->getValue());
        $this->assertEquals(132, Bitmap::createFromArray([7, 2])->getValue());
        $this->assertEquals(7, Bitmap::createFromArray([0, 1, 2])->getValue());
        $this->assertEquals(7, Bitmap::createFromArray([1, 2, 0])->getValue());
        $this->assertEquals(7, Bitmap::createFromArray([2, 1, 0])->getValue());
        $this->assertEquals(6, Bitmap::createFromArray([1, 2])->getValue());
        $this->assertEquals(5, Bitmap::createFromArray([0, 2])->getValue());
        $this->assertEquals(4, Bitmap::createFromArray([2])->getValue());
        $this->assertEquals(1, Bitmap::createFromArray([0])->getValue());
        $this->assertEquals(0, Bitmap::createFromArray([])->getValue());

        $this->assertTrue(Bitmap::create(132)->isEqualWith(Bitmap::create([2, 7])));
        $this->assertTrue(Bitmap::create(132)->isEqualWith(Bitmap::create([7, 2])));
        $this->assertTrue(Bitmap::create(7)->isEqualWith(Bitmap::create([0, 1, 2])));
        $this->assertTrue(Bitmap::create(7)->isEqualWith(Bitmap::create([1, 2, 0])));
        $this->assertTrue(Bitmap::create(7)->isEqualWith(Bitmap::create([2, 1, 0])));
        $this->assertTrue(Bitmap::create(6)->isEqualWith(Bitmap::create([1, 2])));
        $this->assertTrue(Bitmap::create(5)->isEqualWith(Bitmap::create([0, 2])));
        $this->assertTrue(Bitmap::create(4)->isEqualWith(Bitmap::create([2])));
        $this->assertTrue(Bitmap::create(1)->isEqualWith(Bitmap::create([0])));
        $this->assertTrue(Bitmap::create(0)->isEqualWith(Bitmap::create([])));

        $bm1 = Bitmap::create(10);
        $bm2 = Bitmap::create($bm1);
        $this->assertTrue($bm1->isEqualWith($bm2));
        $this->assertFalse($bm1 === $bm2);
    }

    public function testParse()
    {
        $this->assertEquals([2, 7], (new Bitmap(132))->toArray());
        $this->assertEquals([0, 1, 2], (new Bitmap(7))->toArray());
        $this->assertEquals([1, 2], (new Bitmap(6))->toArray());
        $this->assertEquals([0, 2], (new Bitmap(5))->toArray());
        $this->assertEquals([2], (new Bitmap(4))->toArray());
        $this->assertEquals([0], (new Bitmap(1))->toArray());
        $this->assertEquals([], (new Bitmap(0))->toArray());
    }

    public function testIntersections()
    {
        $this->assertFalse($this->intersectionExists([], []));
        $this->assertFalse($this->intersectionExists([], 0));
        $this->assertFalse($this->intersectionExists(0, []));
        $this->assertFalse($this->intersectionExists(0, 0));

        $this->assertTrue($this->intersectionExists([0, 1, 2], [1, 2, 0]));
        $this->assertTrue($this->intersectionExists(7, [1, 2, 0]));
        $this->assertTrue($this->intersectionExists([0, 1, 2], 7));
        $this->assertTrue($this->intersectionExists(7, 7));

        $this->assertTrue($this->intersectionExists([1, 2], [1, 2, 0]));
        $this->assertTrue($this->intersectionExists(6, [1, 2, 0]));
        $this->assertTrue($this->intersectionExists([1, 2, 0], 6));
        $this->assertTrue($this->intersectionExists([1, 2], 7));
        $this->assertTrue($this->intersectionExists(7, [1, 2]));

        $this->assertTrue($this->intersectionExists([2], [1, 2, 0]));
        $this->assertTrue($this->intersectionExists(4, [1, 2, 0]));
        $this->assertTrue($this->intersectionExists([1, 2, 0], 4));
        $this->assertTrue($this->intersectionExists([2], 7));
        $this->assertTrue($this->intersectionExists(7, [2]));
        $this->assertTrue($this->intersectionExists(7, 1));

        $this->assertTrue($this->intersectionExists([2, 7], [1, 2, 0]));
        $this->assertTrue($this->intersectionExists([2, 7], 7));
        $this->assertTrue($this->intersectionExists(7, [2, 7]));
        $this->assertTrue($this->intersectionExists(132, [1, 2, 0]));
        $this->assertTrue($this->intersectionExists([2, 7], 132));

        $this->assertFalse($this->intersectionExists([], [1, 2, 0]));
        $this->assertFalse($this->intersectionExists(0, [1, 2, 0]));
        $this->assertFalse($this->intersectionExists([1, 2, 0], []));
        $this->assertFalse($this->intersectionExists([1, 2, 0], 0));

        $this->assertFalse($this->intersectionExists([5, 7], [1, 2, 0]));
        $this->assertFalse($this->intersectionExists([5, 7], 7));
        $this->assertFalse($this->intersectionExists([1, 2, 3], [5, 7]));
        $this->assertFalse($this->intersectionExists(7, [5, 7]));
    }

    public function testInclusions()
    {
        $this->assertTrue($this->inclusionExists([], []));
        $this->assertTrue($this->inclusionExists(0, []));
        $this->assertTrue($this->inclusionExists([], 0));

        $this->assertTrue($this->inclusionExists([0, 1, 2], [0]));
        $this->assertTrue($this->inclusionExists([0, 1, 2], 1));
        $this->assertTrue($this->inclusionExists(7, [0]));
        $this->assertTrue($this->inclusionExists(7, 1));
        $this->assertTrue($this->inclusionExists([0, 1, 2], [1]));
        $this->assertTrue($this->inclusionExists([0, 1, 2], [2]));

        $this->assertFalse($this->inclusionExists([0], [0, 1, 2]));
        $this->assertFalse($this->inclusionExists(1, [0, 1, 2]));
        $this->assertFalse($this->inclusionExists([0], 7));
        $this->assertFalse($this->inclusionExists(1, 7));
        $this->assertFalse($this->inclusionExists([1], [0, 1, 2]));
        $this->assertFalse($this->inclusionExists([2], [0, 1, 2]));

        $this->assertTrue($this->inclusionExists(133, [2, 7]));
        $this->assertTrue($this->inclusionExists(133, [0, 7]));
        $this->assertFalse($this->inclusionExists(133, [1, 7]));
        $this->assertFalse($this->inclusionExists(133, [0, 1, 7]));
    }

    public function testHasBit()
    {
        $bm = Bitmap::create([1, 3]);
        $this->assertTrue($bm->hasBit(1));
        $this->assertFalse($bm->hasBit(2));
        $this->assertTrue($bm->hasBit(3));
    }

    public function testAddSub()
    {
        $bm = Bitmap::create([]);

        $bm = $bm->add(Bitmap::create([1]));
        $this->assertEquals([1], $bm->toArray());

        $bm = $bm->add([0]);
        $this->assertEquals([0, 1], $bm->toArray());

        $bm = $bm->add([2]);
        $this->assertEquals([0, 1, 2], $bm->toArray());

        $bm = $bm->add(Bitmap::create([1, 2]));
        $this->assertEquals([0, 1, 2], $bm->toArray());

        $bm = $bm->sub([1, 2, 3]);
        $this->assertEquals([0], $bm->toArray());

        $bm = $bm->sub([0, 1]);
        $this->assertEquals([], $bm->toArray());
    }

    protected function intersectionExists($lhs, $rhs): bool
    {
        return Bitmap::create($lhs)->intersectsWith($rhs);
    }

    protected function inclusionExists($lhs, $rhs): bool
    {
        return Bitmap::create($lhs)->includes($rhs);
    }
}
