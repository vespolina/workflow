<?php

/**
 * (c) 2013 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\Tests\Workflow;

use Vespolina\Tests\WorkflowCommon;

class TokenTest extends \PHPUnit_Framework_TestCase
{
    protected $token;

    public function setUp()
    {
        $this->token = WorkflowCommon::createToken();
    }

    /**
     * @test
     */
    public function it_returns_null_on_non_existing_data()
    {
        $this->assertNull($this->token->getData('missing'), 'non-existent data should return null');
    }

    /**
     * @test
     */
    public function it_returns_set_data_when_it_exists()
    {
        $reaction = 'Yeah, bitch! Magnets! Oh!';
        $this->token->setData('laptop', $reaction);
        $this->assertSame($reaction, $this->token->getData('laptop'), 'the set data should be returned');
        $this->token->unsetData('laptop');
        $this->assertNull($this->token->getData('laptop'), 'there should be no data now');
    }

    /**
     * @test
     */
    public function it_should_be_ok_to_unset_non_existing_data()
    {
        $this->token->unsetData('missing', 'there should be no problem with unsetting missing data');
    }

    /**
     * @test
     */
    public function it_should_return_all_data_when_no_argument_key_provided()
    {
        $this->token->setData('payment-details', array('cc' => '1234', 'exp' => '11/11'));
        $this->token->setData('client-points', 13);
        $this->assertCount(2, $this->token->getData(), 'there should be 2 data items');
    }
}