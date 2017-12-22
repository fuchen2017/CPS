<div id="radiodiv" style="width:1000px; height:800px; overflow:hidden; position:relative">
    <iframe src="http://cosci.tw/run/?name=Ck1Caz1503463765710"   height="1000" width="1000"  id="radioframe" style="position:absolute;top:-50px" scrolling="no"  marginwidth="0" marginheight="0" frameborder="0"></iframe>
</div>


<!--操作區-->
        <div class="operate" >
            <section>
				<h2 id="step_dsc">說明 1：實驗結果分享</h2>
				<div class="button_tips" id="show_module_area_btn" onclick="show_module_area()" title="確定" style="display:none;" >確定</div>
				<div id="module_area" style="" ><!-- 模組出題區域-->

				<div class="science_0"><p style="text-align:center"><span style="font-size:28px">實驗結果分享對話1操作區</span></p>
</div>
				<br>
				<div  id="show_msg_box"  class="show_msg_box" >

				</div>
								</div>
            </section>
        </div>
        <!--操作區end-->    </td>
        <!--操作區-->
               <div class="operate" >
                   <section>
       				<h2 id="step_dsc">說明 1：test_talk</h2>
       				<div class="button_tips" id="show_module_area_btn" onclick="show_module_area()" title="確認" style="display:none;" >確認</div>
       				<div id="module_area" style="" ><!-- 模組出題區域-->

       				<div class="science_0"><p><iframe frameborder="0" height="600" scrolling="no" src="http://cosci.tw/run/?name=Ck1Caz1503463765710" width="1100"></iframe></p>
       </div>
       				<br>
       				<div  id="show_msg_box"  class="show_msg_box" >

       				</div>
       								</div>
                   </section>
               </div>
               <!--操作區end-->    </td>

               <!--操作區-->
               <div class="operate" >
                   <section>
               <h2 id="step_dsc"><?php echo $q_d_dsc;?></h2>
               <div class="button_tips" id="show_module_area_btn" onclick="show_module_area()" title="<?php echo $hideModuleBtnDsc;?>" style="<?php if(!$hideModuleArea){echo "display:none;";}?>" ><?php echo $hideModuleBtnDsc;?></div>
               <div id="module_area" style="<?php if($hideModuleArea){echo "display:none;";}?>" ><!-- 模組出題區域-->
               <?php echo $module_html;?>				</div>
                   </section>
               </div>
               <!--操作區end-->    </td>

<iframe src="http://cosci.tw/run/?name=Ck1Caz1503463765710"   height="1000" width="1000"  id="radioframe" style="position:absolute;top:-50px"></iframe>

<iframe frameborder="0" height="600" scrolling="no" src="http://cosci.tw/run/?name=Ck1Caz1503463765710" width="1100">
