<?php
/**
 * 微信公众平台 PHP SDK 示例文件
 *
 * @author NetPuter <netputer@gmail.com>
 */
  require('../src/Wechat.php');

  /**
   * 微信公众平台演示类
   */
  class MyWechat extends Wechat {

    /**
     * 用户关注时触发，回复「欢迎关注」
     *
     * @return void
     */
    protected function onSubscribe() {
      $this->responseText('欢迎关注');
    }

    /**
     * 用户取消关注时触发
     *
     * @return void
     */
    protected function onUnsubscribe() {
      // 「悄悄的我走了，正如我悄悄的来；我挥一挥衣袖，不带走一片云彩。」
    }

    /**
     * 收到文本消息时触发，回复收到的文本消息内容
     *
     * @return void
     */
    protected function onText() {
        $con = mysql_connect("218.244.137.223","yunfeng","yunfeng");
        if(!$con) $this->responseText('未知错误，请致电18868107778');
		mysql_select_db("YFnews", $con);
        $User = $this->getRequest('fromUserName');
		$Content = $this->getRequest('content');
        $NInfo = explode("+",$Content);
        if($Content == "约稿"&&$con)
		{
            $this->responseText('请按以下格式输入进行约稿：预约+时间+地点(此处填写校区即可)+联系人+联系人电话，例如：预约+2014-5-2+紫金港+张三+18888888888（注意分割的符号为‘+’加号哦）. 小贴士：因为我们的部分工作人员没有移动的短号，请留下你的长号哦，请提前至少两天预约哦。提示预约成功后，我们的工作人员会尽快与你取得联系的。Tips:输入“约稿样式”可获取约稿所需输入的标准格式，复制到对话框后修改部分内容即可轻松约稿');
		}
        else if($Content == "YFNNyuegaoR")
		{
			$sql1 = "select * from Register where UserName = '" . $User . "'";
			$test = mysql_query($sql1);
            $test1 = mysql_fetch_array($test);
			if($test1 != NULL)
			{
				$this->responseText('你的账号已经在约稿系统中完成注册了哦，不能重复注册的哦。');
				return;
			}
			else
			{
				$sql2 = "insert into Register values('". $User . "')";
                $Inse = mysql_query($sql2);
				if (!$Inse)
				{
					$this->responseText('非常遗憾，注册失败了，请重试或者联系我们的工作人员吧');
					return;
				}
				else
				{
                    $this->responseText('注册成功，您以后可以通过本平台进行新闻约稿。');
					return;
				}
			}
		}
        else if($NInfo[0]=="预约")
		{
			$sql3 = "select UserName as UN from Register where UserName = '" . $User . "'";
			$test3 = mysql_query($sql3);
			$r3 = mysql_fetch_array($test3);
			if($r3==NULL)
			{
				$this->responseText('您的账号没有在本平台注册，请先进行注册，注册方法请联系18868107778赵同学');
				return;
			}
            if($NInfo[4]<13000000000||$NInfo[4]>18999999999)
            {
                $this->responseText('输入的电话号码格式有误哦，请重新输入');
				return;
            }
            else
            {
				$now = date("Y-m-d");
				$time1 = strtotime($now);
				$time2 = strtotime($NInfo[1]);
				$diff = ($time2 - $time1)/3600;
                if($NInfo == "紫金港") $xq = 1;
				if($diff<48)
				{
					$this->responseText('请检查刚才输入的日期格式是否有误，本系统仅能接受提前至少两天的约稿，给您带来的不便，云峰新网中心深感抱歉。v3');
					return;
				}
                else if($NInfo[2] != "紫金港"&&$NInfo[2] != "玉泉"&&$NInfo[2] != "西溪"&&$NInfo[2] != "华家池"&&$NInfo[2] != "之江")
                    $this->responseText('输入的校区名称有误，可以识别的校区名称为：紫金港、玉泉、西溪、华家池、之江');
                else
                {
                    $sql5 = "select count(phone) as phone from Information where phone = ". $NInfo[4]. " group by phone";
					$test5 = mysql_query($sql5);
                    if($test5!=NULL)
                    {
						$r4 = mysql_fetch_array($test5);
                        if($r4['phone']>=2)
                        {
                            $this->responseText('系统中已经有两条与你的电话关联且未经工作人员处理的预约记录，请耐心等待工作人员的回复。每个电话号码同一时间仅能产生两条预约记录哦');
                            return;
                        }
                    }
                    $sql6 = "select count(date) as date from Information where date = '". $NInfo[1]. "' group by date";
					$test6 = mysql_query($sql6);
                    if($test6!=NULL)
                    {
						$r6 = mysql_fetch_array($test6);
                        if($r6['date']>=8)
                        {
                            $this->responseText('对不起，'.$NInfo[1].'当天的新闻预约数量已经达到饱和，如需帮助，请致电18868112389');
                            return;
                        }
                    }                   
                    $XQ =  ($NInfo[2]=="紫金港")?1:0;
                    $sql4 = "insert into `Information`(`date`, `campus`, `phone`) values('" . $NInfo[1] . "'," . $XQ . "," . $NInfo[4] . ")";
                    
					if (!mysql_query($sql4,$con))
					{
                 
						$this->responseText('非常遗憾，约稿失败了，请重试联系我们的工作人员吧');
						return;
					}                  
                    
					else
					{   
                        $sql7 = "insert into News_Details values('" . $NInfo[1] . "', '" . $Content . "')";
                        mysql_query($sql7,$con);
                        //$mail = new SaeMail();
                        //$ret = $mail->quickSend('984099388@qq.com' , '123' , '123' , 'yftestm@sina.com' , 'yunfeng' ,25);
                        //if($ret == false)$this->responseText('非常遗憾，约稿失败了，请重试联系我们的工作人员吧');
                  	  	$this->responseText('约稿成功，我们的工作人员将提前至少24小时内与您取得联系，如果超过24小时没有得到电话或者短信回复，请致电18868112389');
						return;                        
					}
                }
            }
            
		}
        else 
        if($Content == "昨日新闻"||$Content == "昨日"||$Content == "昨日的新闻"||$Content == "昨天的新闻"||$Content == "昨天")
        {
            $sqll = "select * from Yesterday_News";
            $resu = mysql_query($sqll);
            if($resu == NULL)
            {
                $this->responseText('数据错误，抱歉该功能无法正常使用了/::-|');
				return;
            }
            $r1 = mysql_fetch_array($resu);
            $i1 = new NewsResponseItem($r1[1],$r1[2],$r1[3],$r1[4]);
			$r2 = mysql_fetch_array($resu);
            $i2 = new NewsResponseItem($r2[1],$r2[2],$r2[3],$r2[4]);
            $r3 = mysql_fetch_array($resu);
            $i3 = new NewsResponseItem($r3[1],$r3[2],$r3[3],$r3[4]);
            $items = array(
                $i1,$i2,$i3
                //new NewsResponseItem($r1[1],$r1[2],$r1[3],$r1[4])
      		);
      		$this->responseNews($items);
            return;
        }
        else
        {
            $sqlk = "select * from `Keys` where SKeys =  '" . $Content . "'";
            $kres = mysql_query($sqlk);
            $ress = mysql_fetch_array($kres);
            if($ress == NULL)
            {
                $this->responseText('非常抱歉，我还听不懂您的意思/::-|，要不尝试一下输入“帮助”吧/::D，或者请等待我们工作人员的人工回复吧');
				return;
            }
            else
            {
                $kres = mysql_query($sqlk);
           		$res = mysql_fetch_array($kres);
                $this->responseText($res[1]);
				return;
            }
        }
        mysql_close($con);
    }

    /**
     * 收到图片消息时触发，回复由收到的图片组成的图文消息
     *
     * @return void
     */
    protected function onImage() {
      $items = array(
        new NewsResponseItem('【第二课堂】云峰学园2012级和2013级同学第二课堂项目审核', '【第二课堂】云峰学园2012级和2013级同学第二课堂项目审核', 'http://mmbiz.qpic.cn/mmbiz/plxiaI1Nwv4b5JAfY32FkqRyWtibNlbjcqW5DECOqicTOIaK6HUpl2DlkMOLV3V3FibSKicicicL9lOUia8mTJVdVlicTpA/0', 'http://mp.weixin.qq.com/s?__biz=MzA4OTAxMTUwNg==&mid=200159775&idx=1&sn=b17d1539b2eddfbc0b71d6bab66c68eb#rd'),
        new NewsResponseItem('标题二', '描述二', $this->getRequest('picurl'), $this->getRequest('picurl')),
      );

      $this->responseNews($items);
    }

    /**
     * 收到地理位置消息时触发，回复收到的地理位置
     *
     * @return void
     */
    protected function onLocation() {
      $this->responseText('收到了位置消息：' . $this->getRequest('location_x') . ',' . $this->getRequest('location_y'));
    }

    /**
     * 收到链接消息时触发，回复收到的链接地址
     *
     * @return void
     */
    protected function onLink() {
      $this->responseText('收到了链接：' . $this->getRequest('url'));
    }

    /**
     * 收到未知类型消息时触发，回复收到的消息类型
     *
     * @return void
     */
    protected function onUnknown() {
      $this->responseText('收到了未知类型消息：' . $this->getRequest('msgtype'));
    }

  }

  $wechat = new MyWechat('weixin', TRUE);
  $wechat->run();
