<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit\Converter;

use PommProject\Foundation\Test\Unit\Converter\BaseConverter;

class PgInterval extends BaseConverter
{
    public function testFromPg()
    {
        $this
            ->variable($this->newTestedInstance()->fromPg(null, 'interval', $this->getSession()))
            ->isNull()
            ->dateInterval($this->newTestedInstance()->fromPg('P14346DT22H47M3.138892S', 'interval', $this->getSession()))
            ->isEqualTo(new \DateInterval('P14346DT22H47M3S')) // <- truncated to second precision
            ;
    }

    public function testToPg()
    {
        $this
            ->string($this->newTestedInstance()->toPg(null, 'interval', $this->getSession()))
            ->isEqualTo('NULL::interval')
            ->string($this->newTestedInstance()->toPg(new \DateInterval('P14346DT22H47M3S'), 'interval', $this->getSession()))
            ->isEqualTo("interval '00 years 00 months 14346 days 22:47:03'")
            ;
    }
}
