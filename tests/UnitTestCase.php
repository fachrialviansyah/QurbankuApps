<?php

class UnitTestCase extends CIPHPUnitTestUnitTestCase
{
    public function failed()
    {
        $output = $this->request('GET', 'api/c_pelanggan');
        $this->assertContains(
            'true', $output
        );
        //hasilnya apa 
    }
}
