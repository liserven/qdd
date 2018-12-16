<?php
namespace app\index\controller;

use think\Db;
use think\Request;
/**
 * Class Index
 * @package home\index\controller
 * @return 首页页面
 */
class Index
{
    protected $request;
    public function __construct()
    {
        if (null === $this->request) {
            $this->request = Request::instance();
        }
    }
    public function index(){
//        $json='{"data":{"banner":[{"img":"http://www.999000.cn/data/files/poster/1522032210201803261043307726.jpg","link":"http://jlmp.990000.cn/","type":"0"},{"img":"http://www.999000.cn/data/files/poster/1522032233201803261043538371.jpg","link":"http://jlmp.990000.cn/","type":"0"},{"img":"http://www.999000.cn/data/files/poster/1522032433201803261047134133.jpg","link":"http://jlmp.990000.cn/","type":"0"},{"img":"http://www.999000.cn/data/files/poster/1522032349201803261045493715.jpg","link":"http://jlmp.990000.cn/","type":"0"},{"img":"http://www.999000.cn/data/files/poster/1522032335201803261045359264.jpg","link":"http://jlmp.990000.cn/","type":"0"}],"two":[{"name":"礼遇新年 开年预售","detail":"皮具/服装/金镶玉","info":[{"img":"http://jlmp.999000.cn/public/Public/app/home_two_0.png","link":"","type":"0"},{"img":"http://jlmp.999000.cn/public/Public/app/home_two_1.png","link":"","type":"0"},{"img":"http://jlmp.999000.cn/public/Public/app/home_two_2.png","link":"","type":"0"}]},{"name":"游戏新上线 乐享不停","detail":"竞技/娱乐/休闲","info":[{"img":"http://jlmp.999000.cn/public/Public/app/home_game_1.png","link":"","type":"0"},{"img":"http://jlmp.999000.cn/public/Public/app/home_game_2.png","link":"","type":"0"},{"img":"http://jlmp.999000.cn/public/Public/app/home_game_3.png","link":"","type":"0"}]}],"classify":{"title":"商品分类","info":[{"img":"http://jlmp.999000.cn/public/Public/app/home_good_classify_0.png","link":"","type":"0"},{"img":"http://jlmp.999000.cn/public/Public/app/home_good_classify_1.png","link":"","type":"0"},{"img":"http://jlmp.999000.cn/public/Public/app/home_good_classify_2.png","link":"","type":"0"},{"img":"http://jlmp.999000.cn/public/Public/app/home_good_classify_3.png","link":"","type":"0"},{"img":"http://jlmp.999000.cn/public/Public/app/home_good_classify_4.png","link":"","type":"0"}]},"news":{"title":"新品上架","info":[{"img":"http://jlmp.999000.cn/public/Public/app/home_new0.png","link":"","type":"0"},{"img":"http://jlmp.999000.cn/public/Public/app/home_new1.png","link":"","type":"0"},{"img":"http://jlmp.999000.cn/public/Public/app/home_new2.png","link":"","type":"0"}]},"recommend":{"title":"精品推荐","info":[{"img":"http://jlmp.999000.cn/public/Public/app/home_recomm0.png","link":"","type":"0"},{"img":"http://jlmp.999000.cn/public/Public/app/home_recomm1.png","link":"","type":"0"},{"img":"http://jlmp.999000.cn/public/Public/app/home_recomm2.png","link":"","type":"0"},{"img":"http://jlmp.999000.cn/public/Public/app/home_recomm3.png","link":"","type":"0"}]},"goods":[{"title":"热销商品","id":"9275","img":"http://jlmp.999000.cn/public/Public/app/home_recommed_good0.png","name":"六世缘 S925珍珠项链1","price":"478.00","num":"12"},{"title":"热销商品","id":"9275","img":"http://jlmp.999000.cn/public/Public/app/home_recommed_good0.png","name":"六世缘 S925珍珠项链","price":"478.00","num":"12"},{"title":"热销商品","id":"9275","img":"http://jlmp.999000.cn/public/Public/app/home_recommed_good0.png","name":"六世缘 S925珍珠项链","price":"478.00","num":"12"},{"title":"热销商品","id":"9275","img":"http://jlmp.999000.cn/public/Public/app/home_recommed_good0.png","name":"六世缘 S925珍珠项链","price":"478.00","num":"12"},{"title":"热销商品","id":"9275","img":"http://jlmp.999000.cn/public/Public/app/home_recommed_good0.png","name":"六世缘 S925珍珠项链","price":"478.00","num":"12"},{"title":"热销商品","id":"9275","img":"http://jlmp.999000.cn/public/Public/app/home_recommed_good0.png","name":"六世缘 S925珍珠项链","price":"478.00","num":"12"},{"title":"热销商品","id":"9275","img":"http://jlmp.999000.cn/public/Public/app/home_recommed_good0.png","name":"六世缘 S925珍珠项链","price":"478.00","num":"12"},{"title":"热销商品","id":"9275","img":"http://jlmp.999000.cn/public/Public/app/home_recommed_good0.png","name":"六世缘 S925珍珠项链","price":"478.00","num":"12"},{"title":"热销商品","id":"9275","img":"http://jlmp.999000.cn/public/Public/app/home_recommed_good0.png","name":"六世缘 S925珍珠项链","price":"478.00","num":"12"},{"title":"热销商品","id":"9275","img":"http://jlmp.999000.cn/public/Public/app/home_recommed_good0.png","name":"六世缘 S925珍珠项链","price":"478.00","num":"12"},{"title":"热销商品","id":"9275","img":"http://jlmp.999000.cn/public/Public/app/home_recommed_good0.png","name":"六世缘 S925珍珠项链","price":"478.00","num":"12"},{"title":"热销商品","id":"9275","img":"http://jlmp.999000.cn/public/Public/app/home_recommed_good0.png","name":"六世缘 S925珍珠项链","price":"478.00","num":"12"},{"title":"热销商品","id":"9275","img":"http://jlmp.999000.cn/public/Public/app/home_recommed_good0.png","name":"六世缘 S925珍珠项链","price":"478.00","num":"12"}]},"status":"0","msg":"获取成功"}';
//return $json;
        $page=$this->request->param('page');
        $page=!empty($page)?$page:1;
        if($page==1){

            $banner=Db::name('adlist')->field('AdPicture as img,AdLinkUrl as link,type')->where('CategoryId=19')->order('id asc')->select();
            foreach ($banner as $k=>$v){
                $banner[$k]['img']=config('IMAGE_DOMAIN_NAME'). $banner[$k]['img'];
            }
            $arr['data']['banner']=$banner;
            $yushou=Db::name('adlist')->field('AdPicture as img,AdLinkUrl as link,type')->where('CategoryId=20')->order('id asc')->select();
            foreach ($yushou as $k=>$v){
                $yushou[$k]['img']=config('IMAGE_DOMAIN_NAME'). $yushou[$k]['img'];
            }
            $yushous['name']='礼遇新年 开年预售';
            $yushous['detail']='皮具/服装/金镶玉';
            $yushous['info']=$yushou;
            $arr['data']['two'][0]=$yushous;
            $youxi=Db::name('adlist')->field('AdPicture as img,AdLinkUrl as link,type')->where('CategoryId=21')->order('id asc')->select();

            foreach ($youxi as $k=>$v){
                $youxi[$k]['img']=config('IMAGE_DOMAIN_NAME'). $youxi[$k]['img'];
            }
            $youxis['name']='游戏新上线 乐享不停';
            $youxis['detail']='竞技/娱乐/休闲';
            $youxis['info']=$youxi;
            $arr['data']['two'][1]=$youxis;

            $fenlei=Db::name('adlist')->field('AdPicture as img,AdLinkUrl as link,type')->where('CategoryId=22')->order('id asc')->select();
            foreach ($fenlei as $k=>$v){
                $fenlei[$k]['img']=config('IMAGE_DOMAIN_NAME'). $fenlei[$k]['img'];
            }
            $fenleis['title']='商品分类';
            $fenleis['info']=$fenlei;
            $arr['data']['classify']=$fenleis;
            $news=Db::name('adlist')->field('AdPicture as img,AdLinkUrl as link,type')->where('CategoryId=23')->order('id asc')->select();
            foreach ($news as $k=>$v){
                $news[$k]['img']=config('IMAGE_DOMAIN_NAME'). $news[$k]['img'];
            }
            $newss['title']='新品上架';
            $newss['info']=$news;
            $arr['data']['news']=$newss;
            $recommend=Db::name('adlist')->field('AdPicture as img,AdLinkUrl as link,type')->where('CategoryId=24')->order('id asc')->select();
            foreach ($recommend as $k=>$v){
                $recommend[$k]['img']=config('IMAGE_DOMAIN_NAME'). $recommend[$k]['img'];
            }
            $recommends['title']='精品推荐';
            $recommends['info']=$recommend;
            $arr['data']['recommend']=$recommends;
            $goods=Db::name('product')->field('ProName as name,qiqiuproimgpath,ProId as id,ProImg as img,MaxProImg,VipPrice as price,prosum as num')->where('IsOnSell=1')->order('prosum desc')->limit(($page-1)*20,20)->select();
            foreach ($goods as $k=>$v){
                $goods[$k]['title']='热销商品';
                if(is_onqiniu()==true){

                    $goods[$k]['img']= $goods[$k]['qiqiuproimgpath'];
                }else{
                    $goods[$k]['img']=config('IMAGE_DOMAIN_NAME').'/public/Upload/cpimg/'. $goods[$k]['img'];
                }
            }
            $arr['data']['goods']=$goods;
            $arr['status']=0;
            $arr['msg']='获取成功';
        }else{
            $list=Db::name('product')->field('ProName as name,qiqiuproimgpath,ProId as id,ProImg ,MaxProImg,VipPrice as price,prosum as num')->where('IsOnSell=1')->order('prosum desc')->limit(($page-1)*20,20)->select();
            if(!empty($list)){
                foreach ($list as $k=>$v){
                    $list[$k]['title']='热销商品';
                    if(is_onqiniu()==true){
                        $list[$k]['img']= $list[$k]['qiqiuproimgpath'];
                    }else{
                        $list[$k]['img']=config('IMAGE_DOMAIN_NAME').'/public/Upload/cpimg/'. $list[$k]['img'];
                    }
                }
                $arr['data']['goods']=$list;
                $arr['status']=0;
                $arr['msg']='获取成功';
            }else{
                $arr['status']=1;
                $arr['msg']='暂无数据';
            }

        }
        return json($arr);
    }
    public function version_ios(){
        $verdata['version_code'] = "1";
        $verdata['version_name'] = "1.0";
        $verdata['is_show'] = "1";
        $verdata['update_content'] = "新功能上线了";

        $data['data'] = $verdata;
        $data['status'] = "0";
        $data['msg'] = "版本获取成功";
        return json($data);
    }
    public function version_android(){
        $verdata['version_code'] = "1";
        $verdata['version_name'] = "1.0";
        $verdata['is_show'] = "1";
        $verdata['update_content'] = "新功能上线了";

        $data['data'] = $verdata;
        $data['status'] = "0";
        $data['msg'] = "版本获取成功";
        return json($data);
    }
}
