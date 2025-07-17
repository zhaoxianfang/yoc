<?php

namespace Modules\Spider\Http\Controllers\Web;

use Modules\System\Http\Controllers\BaseController;
use zxf\Dom\Document;

class SpiderController extends BaseController
{
    /**
     * SpiderController constructor.
     * 插入数据
     */
    public function index()
    {

        return '';
        // dd($this->test());
    }

    public function test()
    {
        $document = new Document('https://www.gov.cn/zhengce/zuixin/home.htm', true);
        $titles = $document->find('//h4/a', 'XPATH');
        foreach ($titles as $title) {
            echo $title->text(), "\n";
        }
        dd($titles);

        $document = new Document('https://www.gov.cn/zhengce/202309/content_6903979.htm', true);
        $titles = $document->find('//*[@id="UCAP-CONTENT"]', 'XPATH');
        foreach ($titles as $title) {
            echo $title->text(), "\n";
            dump($this->getTimeOrDate($title->text()));
            dump($this->getTimeOrDate($title));
        }
        dd($titles);
    }

    // 正则匹配时间格式 日期格式 preg_match_all | preg_match, 支持格式:2022-05-30,2022/05/30,2022.05.30,2022年05月30日,2022-05-30 12:12:12
    private function getTimeOrDate(?string $string)
    {
        if (! empty($string) && preg_match("/(\d{2,4})(-|\/|.|,|、|年|\s)(\d{1,2})(-|\/|.|,|、|月|\s)(\d{1,2})(日)?(\s+(\d{1,2})\:(\d{1,2})\:(\d{1,2}))?/", $string, $parts)) {
            if (isset($parts[0]) && ! empty($parts[0])) {
                $string = $parts[0];
            }
        }

        return $string;
    }
}
