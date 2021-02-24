<?php

class TestCase extends CIPHPUnitTestCase
{
    public function test_index()
    {
        $output = $this->request('GET', 'welcome/index');
        $this->assertContains(
            '<title>Welcome to CodeIgniter</title>', $output
        );
        //hasilnya apa 
    }
}
