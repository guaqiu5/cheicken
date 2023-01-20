<template>
	<div>
        <h1 align="center">数字逻辑实验常见问题</h1>

        <p class="p_content_with_no">数字逻辑和数字电子技术实验采用FPGA实验方式，本次实验可以采用本地FPGA实验和远程在线FPGA实验相结合。</p>
        <p class="p_warn">注意:</p>
        <p class="p_content_with_no">（1）本地FPGA实验，使用的可编程器件是Altera 公司的（Cyclone） 系列的（EP1C12F324C8）芯片。</p> 
        <p class="p_content_with_no">（2）远程在线实验使用的可编程器件是Altera 公司的（Cyclone IV E） 系列的（EP4C10F17C8）芯片。
            <a href="" @click.prevent="help_img_show = help_img_show?false:true;">EP4CE10F17C8引脚锁定对应表</a>
        </p>
        <div v-show="help_img_show">
            <img src="@/assets/help/EP4CE10F17C8.jpg">
        </div>
        <p class="p_content_with_no">（3）实验使用的开发软件是（Quartus II 13.0）。</p>
        <br>
        <p class="p_content_with_no">下面将常见的实验过程中常见的问题列出。</p>

        <p class="p_title">1.在本地如何进行FPGA实验？</p>
        <p class="p_content_with_no">（1）使用Quartus II 13.0将所设计的电路原理图或vhdl语言输入到计算机；</p>
        <p class="p_content_with_no">（2）利用Quartus II 13.0将设计电路编译查错，如果没有错误锁定管脚后，再次编译生成sof文件；</p>
        <p class="p_content_with_no">（3）通过下载电缆下载到可编程器件中进行验证，QuartusII操作视频如下：</p>
        <video width="480" height="320" controls>
            <source src="@/assets/help/quartus.mp4" type="video/mp4"/>
        </video>

        <p class="p_title">2.如何进行远程在线实验？</p>
        <p class="p_content_with_no">（1）先在本地讲设计的电路的原理图或VHDL语言输入到计算机并保存文件。</p>
        <p class="p_content_with_no">（2）使用开发系统编译查错，如果没错锁定管脚后,再次编译生成RBF文件。</p>
        <p class="p_content_with_no">（3）登录网址：http://10.3.2.225:8085/FPGAExpV2/login，登录的用户名和密码均为你的学号，密码登录后自己修改。</p>
        <p class="p_content_with_no">（4）建立虚拟实验面板，建立虚拟操作视频如下：</p>
        <video width="480" height="320" controls>
            <source src="@/assets/help/xunimianban.mp4" type="video/mp4"/>
        </video>
        <p class="p_content_with_no">（5）FPGA烧写，然后运行，远程验证操作视频如下：</p>
        <video width="480" height="320" controls>
            <source src="@/assets/help/run.mp4" type="video/mp4"/>
        </video>

        <p class="p_title">3.建立工程文件时应注意?</p>
        <p class="p_content_with_no">（1）选择工程所在的文件夹，注意文件夹名称最好符合标识符的规定，不能使用汉字。</p>
        <p class="p_content_with_no">（2）输入工程名（不能使用汉字）</p>
        <p class="p_content_with_no">（3）选择正确的器件</p>

        <p class="p_title">4.项目文件编译出错？</p>
        <p class="p_content_with_no">（1）所设计的电路存在语法错误</p>
        <p class="p_content_with_no">（2）没有将文件保存在子文件夹里</p>
        <p class="p_content_with_no">（3）文件夹名称可能带有汉字，必须是英文字符 </p>
        <p class="p_content_with_no">（4）实体名与文件名不一致。</p>

        <p class="p_title">5.生成的sof文件无法下载原因？</p>
        <p class="p_content_with_no">（1）可编程器件选择错误，记住本地实验是EP1C12F324C8，远程在线实验是EP4C10F17C8。</p>
        <p class="p_content">重新选择器件的方法是：在assignment/device命令</p>
        <p class="p_content_with_no">（2）USB Blaster接触不良（重新拔插保证USB blaster盒子上的USB指示灯亮,或关闭实验箱重开再试。</p>
        <p class="p_content_with_no">（3）实验仪没打开电源。</p>
        <p class="p_content_with_no">（4）下载时start按钮灰色不可用，hardware选择错误，点击hardware setup 按钮，重新选择USB-BLASTER即可。</p>
        <p class="p_content">解决start灰色不可用操作视频如下：</p>
        <video width="480" height="320" controls>
            <source src="@/assets/help/nohardware.mp4" type="video/mp4"/>
        </video>

        <p class="p_title">6.如何编译生成rbf格式文件?</p>
        <p class="p_content_with_no">QuartusII编译默认生成sof文件，但在远程在线实验时，FPGA 烧写必须使用rbf格式文件，生成rbf格式文件需进行下列操作：</p>
        <p class="p_content_with_no">在编译之前，选择assignment/device/点击device and options…，</p>
        <p class="p_content_with_no">在category中选择Programming flies，在右侧勾选Raw Binary File（.rbf），</p>
        <p class="p_content_with_no">点击OK即可，如何生成rbf文件，操作视频如下：</p>
        <video width="480" height="320" controls>
            <source src="@/assets/help/rbf.mp4" type="video/mp4"/>
        </video>

        <p class="p_title">7.下载后验证时没有反映原因？</p>
        <p class="p_content_with_no">（1）没有锁定管脚</p>
        <p class="p_content_with_no">（2）管脚锁定后没有二次编译</p>
        <p class="p_content_with_no">（3）没有下载成功</p>
        <p class="p_content_with_no">（4）没有正确给定输入。</p>
            
        <p class="p_title">8.无法进行管脚锁定？</p>
        <p class="p_content_with_no">项目没有编译或编译出错没有成功，</p>
        <p class="p_content_with_no">在锁定管脚的界面，选择view/all pins list显示出所有的输入输出引脚，</p>
        <p class="p_content_with_no">如果没有location，在输入输出引脚窗口，点击右键，在弹出的快捷菜单上选择customize columns...，将location加进来。</p>

        <p class="p_title">9.如何进行仿真？</p>
        <p class="p_content_with_no">（1）先建立仿真波形文件，并给输入信号节点赋值，保存文件;</p>
        <p class="p_content_with_no">（2）然后进行仿真，选择processing菜单下选择simulator tool，进入仿真界面，</p>
        <p class="p_content_with_no">（3）在simulation mode中，选择功能“functional”，选择仿真输入文件，点击“generate functional simulation Netlist”，</p>
        <p class="p_content">网表文件生成成功后，点击“start”，最后，点击“report”查看仿真结果。</p>
        <p class="p_warn">注意：</p>
        <p class="p_content_with_no">Quartus II 13.0下仿真方法与其他版本有区别，在Quartus II13.0 下仿真方法：</p>
        <p class="p_content_with_no">（1）建立仿真波形文件（file/new/university Program VMF）</p>
        <p class="p_content_with_no">（2）插入仿真的输入输出节点并赋值</p>
        <p class="p_content_with_no">（3）在simulation菜单下选择option/Quartus II Simlator</p>
        <p class="p_content_with_no">（4）然后在simulation菜单下选择run functional simulation</p>
        <p class="p_content">如何仿真操作视频如下：</p>
        <video width="480" height="320" controls>
            <source src="@/assets/help/simulation.mp4" type="video/mp4"/>
        </video>

        <p class="p_title">10.如何设计含有多个模块的复杂电路？</p>
        <p class="p_content_with_no">采用自低向上的原则，先设计底层模块，再设计顶层模块，具体方法:</p>
        <p class="p_content_with_no">（1）先设计一个低层模块，将当前设计的模块置顶后再编译方法（project菜单选择“set as top-level entity”）</p>
        <p class="p_content_with_no">（2）再设计第二个低层模块，保存文件后，同样先将当前的模块作为顶层文件再编译 </p>
        <p class="p_content_with_no">（3）按同样的方法将所有低层模块设计好后，最后再设计顶层模块</p>
        <p class="p_content_with_no">（4）最后将顶层模块设为顶层实体后再编译，锁定管脚后二次编译，验证功能</p>
        <p class="p_warn">注意：</p>
        <p class="p_content_with_no">（1）如果顶层采用原理图的方式，须将每个底层模块生成器件模块（file选择create/update在选择子菜单create symbol files for current file）</p>
        <p class="p_content_with_no">（2）工程中所有的文件必须保存在同一个文件夹中。</p>
    </div>
</template>
<script>
    export default {
        name: "commonProblems",
        data(){
			return {
				help_img_show:false
			}
		}
    }
</script>
<style scoped>
    span {
        color: red;
        font-weight: bold;
    }
    .p_title {
        font-weight: bold;
    }
    .p_content_with_no {
        text-indent: 0em;
        line-height: 30%;
    }
    .p_content {
        text-indent: 2.6em;
        line-height: 30%;
    }
    .p_warn {
        color: red;
        font-weight: bold;
        line-height: 50%;
    }
</style>