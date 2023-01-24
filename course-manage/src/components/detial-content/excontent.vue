<template>
	<div class="excontent">
		<el-tabs type="border-card"  v-model="activeName" v-loading="testLoading">
		  <el-tab-pane name="systemInfo">
			  <span slot="label"><i class="el-icon-view"></i> 系统提示</span>
				<p>各位同学，欢迎来到数字逻辑实验室。</p>
				<p>本实验室课程实验成绩有实验预习、实验操作、实验报告三部分按比例构成。</p>
				<p>同学们在实验时按照预习测试、实验操作、实验报告顺序依次完成。</p>
				<p>1.实验预习测试须在规定的时间内完成（一般是15分钟），预习测试由系统自动批改。
					<span style="color:red;font-weight:bold;">做测试题时应注意做完后必须点“交卷”。</span>
				</p>
				<p>2.实验操作主要考察学生实验操作表现，系统先初步评判，教师根据同学们的现场操作情况、完成情况、实验结束后的仪器设备整理情况等综合表现进行最后的评分。
					<span style="color:red;font-weight:bold;">做实验前必须先“签到”。</span>
				</p>
				<p>3.实验报告由若干道思考题构成的，同学可在实验室现场填写，禁止抄袭，实验报告须在一周内提交，实验报告由教师批改。
					<span style="color:red;font-weight:bold;">填写实验报告时应注意每道题必须点“保存”。</span>
				</p>
		  </el-tab-pane>
      <el-tab-pane name="first">
		  	<span slot="label"><i class="el-icon-tickets"></i>实验指导</span>
		  	<div class="third_content" v-if="purpose.length > 0">
		  		<div class="" v-for = "(item,index) in purpose">
		  			<div class="list">{{capital[index]}}、{{item.retitle}}</div>
		  			<div class="list_content" v-html="item.recont">
		  				<!-- {{item.recont}} -->
		  			</div>
		  		</div>
		  	</div>
		  	<div style="height: 500px" v-else>
		    	<p class="tagtitle">暂无实验指导的描述，请联系老师！</p>
		    </div>
		  </el-tab-pane>
		  <el-tab-pane name="second">
		    <span slot="label"><i class="el-icon-document"></i> 预习测试</span>
			<div class="second_title" v-show="!isFinishtest && !isStartTest">
				<el-button type="success" class="test-start" @click="startTestBtn">开始测试</el-button>
				<p></p>
				<span>本次预习测试时长为：{{extime}}分钟</span>
				<span style="color: red;margin-left: 50px;">请注意：开始测试后系统将自动计时，请在规定时间内点“交卷”，超时或未交卷将不计分（刷新或退出浏览器无法终止测试）</span>
			</div>
			<div class="second_title" v-show="!isFinishtest && isStartTest">
				<span>本次预习测试时长为：{{extime}}分钟</span>
				<span style="color: blue;margin-left: 50px;">当前剩余时间：{{qstuRemainTime}}</span>
				<span style="color: red;margin-left: 50px;">请注意：请在规定时间内点“交卷”，超时或未交卷将不计分（刷新或退出浏览器无法终止测试）</span>
			</div>
			<div v-if="questions.length > 0">
		    <div v-for="(item,index) in questions">
		    	<div class="test-header">
		    		{{index+1}}. 
		    		<span style="color: red"> 
		    			[{{item.type == 0 ? '单选题' : '多选题'}}]
		    			{{item.type == 0 ? singleCount : multiCount}}分
		    		</span> 
		    		{{item.topic}}
		    	</div>
				<div v-if="!isFinishtest && !isStartTest">
					
				</div>
		    	<div v-else-if="!isFinishtest && isStartTest">
					<div class="test-select" v-if = "item.type == 0">
						<el-radio-group v-model="stuselect[index].select">
							<el-radio 
								:label="index1" 
								:key="index1" 
								v-for="(anser,index1) in item.soptions" 
								border
								style="">{{num[index1]}} 、 {{anser.value}}</el-radio>
						</el-radio-group>
					</div>
					<div class="test-select" v-else>
						<el-checkbox-group v-model="stuselect[index].select">
							<el-checkbox 
								:label="index1" 
								:key="index1" 
								v-for="(anser,index1) in item.soptions" 
								border
								style="">{{num[index1]}} 、 {{anser.value}}</el-checkbox>
						</el-checkbox-group> 
					</div>
		    	</div>
		    	<div class="pselectdiv" v-else>
		    		<div>
		    			<span>正确答案：</span><span v-for="correct in item.correctArr">{{num[correct]}}</span> 
		    			<span>你的答案：</span><span v-for="stu in item.stuselect">{{num[stu] ? num[stu] : '未选择'}}</span>
		    			<span :class="[item.isture == 1 ? activeClass : errorClass]">({{item.isture == 1 ? '正确' : '错误'}})</span>
		    		</div>
		    		<div v-if="item.isture == 1">
		    			<p v-for="(anser,index1) in item.soptions" class="pselect" :class="[anser.isture == 1 ? activeBorder : '']">{{num[index1]}} 、 {{anser.value}}</p>
		    		</div>
		    		<div v-else>
		    			<p v-for="(anser,index1) in item.soptions" class="pselect" :class="[anser.isture == 1 ? errorBorder : '']">{{num[index1]}} 、 {{anser.value}}</p>
		    		</div>
		    	</div>
<!-- 		    	<div class="test-select">
		    		<div v-for="(anser,index1) in item.soptions" style="margin-bottom: 10px">
		    			<el-radio v-model="radio[index]" :label="index1" border>{{anser.value}}</el-radio>
		    		</div>
		    	</div> -->
		    </div>
		    <el-button type="danger" class="submit" @click="complete" v-show="!isFinishtest && isStartTest">交卷</el-button>
		    </div>
		    <div style="height: 500px" v-else>
		    	<p class="tagtitle">准备开始测试！</p>
		    </div>
		  </el-tab-pane>
		  <el-tab-pane name="third" class="second_content" :disabled="!isFinishtest">
		  	<span slot="label"><i class="el-icon-time"></i>实验签到</span>
		  	<div class="sign">
				<P class="date">当前日期:{{date}}</P> 
				<P class="time">时间:{{time}}</P>  
				<p class="date">签到时间:{{starttime}}</p>	
				<p class="date">实验时长:{{usetime}}</p>
				<p class="date">签退时间:{{endtime}}</p>
				<el-button type="success" @click.stop.prevent="start">签到</el-button>	 
				<el-button type="danger" @click.stop.prevent="stop">签退</el-button>		
		  	</div>
		  </el-tab-pane>
		  <el-tab-pane name="fourth" :disabled="this.starttime == ''">
		  	<span slot="label"><i class="el-icon-edit"></i>填写实验报告</span>
		  	<div>
				<div v-if="ObjectiveQuestions.length > 0">
					<div class="list">{{capital[purpose.length]}}、客观题</div>
					<div v-for="(question, questionIndex) in ObjectiveQuestions">
						<div class="test-header">
							{{questionIndex+1}}. 
							<span style="color: red"> 
								[{{question.type == 1 ? '单选题' : question.type == 2 ? '多选题' : '填空题'}}]
								{{question.score}}分
							</span> 
							{{question.topic}}
						</div>
            <div class="questionImageClass" v-if="question.topicImages" v-for="(image, imageIndex) in question.topicImages">
              <span>[图{{imageIndex+1}}] {{image.shortName}}</span><p></p>
              <img :src="image.imageBase64Content" style="height:50%;width:50%;">
            </div>
						<div v-if="question.isHandIn == 0">
							<div class="test-select" v-if = "question.type == 1">
								<el-radio-group v-model="AnswerResult[questionIndex].answerValue">
									<el-radio 
										:label="optionIndex" 
										:key="optionIndex" 
										v-for="(option, optionIndex) in question.questionOptions" 
										border
										style="">{{num[optionIndex]}} 、 {{option.optionName}}</el-radio>
								</el-radio-group>
							</div>
							<div class="test-select" v-else-if = "question.type == 2">
								<el-checkbox-group v-model="AnswerResult[questionIndex].answerValue">
									<el-checkbox 
										:label="optionIndex" 
										:key="optionIndex" 
										v-for="(option, optionIndex) in question.questionOptions" 
										border
										style="">{{num[optionIndex]}} 、 {{option.optionName}}</el-checkbox>
								</el-checkbox-group> 
							</div>
							<div class="test-select" v-else-if = "question.type == 3">
								<el-input
									placeholder="请写入答案（注：多个答案按顺序用逗号分隔，如：答案1，答案2，答案3）"
									v-model="AnswerResult[questionIndex].answerValue"
									style="width: 85%">		
								</el-input>
							</div>
						</div>
						<div class="pselectdiv" v-else>
							<div>
								<span>正确答案：</span>
								<span v-if="question.type == 1"><!-- 单选题 -->
									{{num[question.correctAnswerValue]}}
								</span>
								<span v-else-if="question.type == 2" v-for="correct in question.correctAnswerValue"><!-- 多选题 -->
									{{num[correct]}}
								</span>
								<span v-else-if="question.type == 3"><!-- 填空题 -->
									{{question.correctAnswerValue}}
								</span>
								<p v-if="question.type == 3"></p>
								<span>你的答案：</span>
								<span v-if="question.type == 1">
									{{num[question.questionResult.answerValue] ? num[question.questionResult.answerValue] : '未选择'}}
								</span>
								<span v-else-if="question.type == 2" v-for="answer in question.questionResult.answerValue">
									{{num[answer] ? num[answer] : '未选择'}}
								</span>
								<span v-else-if="question.type == 3">
									{{question.questionResult.answerValue ? question.questionResult.answerValue : '未填写'}}
								</span>
								<span :class="[question.questionResult.isRight == 1 ? activeClass : errorClass]">
									({{question.questionResult.isRight == 1 ? '正确' : (question.questionResult.isRight == 2 ? '部分正确' : '错误')}}
                   得{{question.questionResult.score}}分)
								</span>
							</div>
							<div v-if="question.questionResult.isRight == 1">
								<p v-if="question.type != 3" v-for="(option,optionIndex) in question.questionOptions" 
									class="pselect" :class="[option.isRight ? activeBorder : '']">
									{{num[optionIndex]}} 、 {{option.optionName}}
								</p>
							</div>
							<div v-else>
								<p v-if="question.type != 3" v-for="(option,optionIndex) in question.questionOptions" 
									class="pselect" :class="[option.isRight ? errorBorder : '']">
									{{num[optionIndex]}} 、 {{option.optionName}}
								</p>
							</div>
						</div>
					</div>
					<div class="btn_save_center" v-if="ObjectiveQuestions[0].isHandIn == 0">
						<el-button type="danger" @click="saveObjective()">保存</el-button>
					</div>
					<p v-else></p>
				</div>
			  	<div class="list">{{capital[purpose.length + (ObjectiveQuestions.length > 0 ? 1 : 0)]}}、实验思考</div>
			  	<div v-if="ponder.length > 0">
					<div v-for="(item,index) in ponder">
						<p>{{index+1}}、{{item.topic}} <span>(本题满分：{{item.score}}分)</span></p>
            <div class="questionImageClass" v-if="item.topicImages" v-for="(image, imageIndex) in item.topicImages">
            <span>[图{{imageIndex+1}}] {{image.shortName}}</span><p></p>
            <img :src="image.imageBase64Content" style="height:50%;width:50%;">
          </div>
						<quill-editor ref="myTextEditor" v-model="content[index]">
						</quill-editor>
						<div class="btnsave"><el-button type="danger" @click="saveSubjective(index)">保存</el-button></div>
					</div>
			  	</div>
			  	<div v-else>
			  		<p style="text-indent: 2em">暂无思考题</p>
			  	</div>
			  	<el-button type="success" class="submit" @click="submitAllForm">提交报告</el-button>
		  	</div>
		  	<!-- <div>
		  		<div class="list">{{capital[purpose.length+1]}}、实验总结</div>
		  		<div>
		  			<p>1．实验总结 （总结本次实验收获，实验中应该注意的事项） </p>
			  		<quill-editor ref="myTextEditor" v-model="summary">
	        		</quill-editor>
	        		<div class="btnsave"><el-button type="danger" @click="saveSummary">保存</el-button></div>
		  		</div>
		  	</div> -->
		  </el-tab-pane>
		  <el-tab-pane name="lastTab">
			  <span slot="label"><i class="el-icon-edit"></i>常见问题</span>
			  <div>
				  <commonProblems></commonProblems>
			  </div>
		  </el-tab-pane>
		</el-tabs>
		<el-dialog
	  title="通知"
	  :visible.sync="centerDialogVisible"
	  width="30%"
	  center>
	  	<p v-if="isPass==1" class="dialogp" style="color: #67C23A"><i class="el-icon-success"></i> 恭喜你，通过测试</p>
	  	<p v-else class="dialogp" style="color: #F56C6C"><i class="el-icon-error"></i> 尚未通过测试！</p>
	 	<p class="dialogp"><span>本次试卷分数：<span style="color: #ff6547">{{Allscore}}</span></span> , <span>及格分数线：<span style="color: #ff6547">{{isPassScore}}</span></span></p>
	  	<p class="dialogp"><span>你的分数：<span style="color: #ff6547">{{stuscore}}</span></span></p>
	  <span slot="footer" class="dialog-footer">
	    <el-button @click="centerDialogVisible = false">取 消</el-button>
	    <el-button type="primary" @click="centerDialogVisible = false">确 定</el-button>
	  </span>
	</el-dialog>
	</div>
</template>
<script>
import {
  getStuQuestion,
  getExperimentContent,
  getExperimentConclusion,
  getExperimentResult,
  submitFinish,
  Signin,
  SigninStart,
  SigninOut,
  submitExreport,
  getStuExreport,
  queryObjectiveQuestionResult,
  saveObjectiveQuestionResult,
} from "@/api/api";
import commonProblems from "./commonProblems.vue";
export default {
  name: "excontent",
  components: {
    commonProblems,
  },
  data() {
    return {
      activeName: "systemInfo",
      week: [
        "星期天",
        "星期一",
        "星期二",
        "星期三",
        "星期四",
        "星期五",
        "星期六",
      ],
      timer: "", //时间函数
      date: "",
      time: "", //实时时间
      starttime: "", //签到时间
      usetime: "", //完成时长
      timestamp: "", //实时时间戳
      startstamp: "", //签到时间戳
      endtime: "", //签退时间
      endstamp: "", //签退时间戳
      stopflag: false, //计时器停止
      content: [], //文档内容
      singleCount: 0, //单选题分数
      multiCount: 0, //多选题分数
      assignQues: 0, //是否需要分配题目：1|是 0|否
      questions: [], //测试题
      stuselect: [], //学生选择
      qstuUseTimeSeconds: 0, // 预习测试已用时长(秒)
      qstuRemainTime: "", // 预习测试剩余时间(时:分:秒)
      testLoading: false, //loding
      purpose: [], //目的及意义
      ponder: [], //实验思考
      user: {}, //用户信息
      summary: "", //实验总结
      isStartTest: 0, // 判断是否开始测试
      isFinishtest: 0, //判断是否交卷
      activeClass: "flagBlue", //正确
      errorClass: "flagRed", //错误
      activeBorder: "BorderBlue", //正确
      errorBorder: "BorderRed", //错误
      centerDialogVisible: false,
      stuscore: 0, //分数
      Allscore: 0, //总分
      isPassScore: 0, //是否通过的分数
      isPass: 0, //是否通过
      experimentTime: 0, //实验时长
      extime: 0, //预测题时长
      num: ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J"], //字母
      capital: [
        "一",
        "二",
        "三",
        "四",
        "五",
        "六",
        "七",
        "八",
        "九",
        "十",
        "十一",
        "十二",
        "十三",
      ],
      ObjectiveQuestions: [], // 客观题题集
      AnswerResult: [], // 客观题答题结果集
    };
  },
  methods: {
    /* 定时器监测更新任务 */
    updateTime() {
      this.checkExperimentTime(); // 监测实验签到模块的实验已用时长是否超时
      this.checkExtime(); // 监测预习测试模块的测试已用时长是否超时
    },
    /* 监测实验签到模块的实验已用时长是否超时 */
    checkExperimentTime() {
      var cd = new Date();
      this.timestamp = Date.parse(cd);
      this.time =
        this.zeroPadding(cd.getFullYear(), 4) +
        "-" +
        this.zeroPadding(cd.getMonth() + 1, 2) +
        "-" +
        this.zeroPadding(cd.getDate(), 2) +
        " " +
        this.zeroPadding(cd.getHours(), 2) +
        ":" +
        this.zeroPadding(cd.getMinutes(), 2) +
        ":" +
        this.zeroPadding(cd.getSeconds(), 2);
      this.date =
        this.zeroPadding(cd.getFullYear(), 4) +
        "-" +
        this.zeroPadding(cd.getMonth() + 1, 2) +
        "-" +
        this.zeroPadding(cd.getDate(), 2) +
        " " +
        this.week[cd.getDay()];
      // 如果实验签到开始时间不为空(已签到) 并且 实验未超时(未停止定时器)，则执行监测任务
      if (this.startstamp != "" && !this.stopflag) {
        // console.log(parseInt(this.experimentTime));
        var arr = this.calculateDiffTime(this.startstamp, this.timestamp); // 返回数组：[时,分,秒]
        let timeInterval = arr[0] * 60 + arr[1]; // 时间间隔(分钟) = 小时 * 60 + 分钟
        // console.log(timeInterval);
        this.usetime =
          this.zeroPadding(arr[0], 2) +
          ":" +
          this.zeroPadding(arr[1], 2) +
          ":" +
          this.zeroPadding(arr[2], 2);
        /**
         * 判断 当前实验时长(分钟) timeInterval 是否 超过(大于等于) 实验规定时长(分钟) experimentTime
         *      若超过了实验规定时长，则自动结束实验答题，并自动执行【签退】操作
         *     【即要保证学生在规定的有限时长内完成实验，超时自动签退】
         *
         * 增加 parseInt(this.experimentTime) > 0 的判断，避免定时器执行到此处时，后端请求数据还未到达，导致默认值 0
         * 最终使条件判断成立，执行签退操作，即：前端点击签到按钮后直接显示签退时间的现象
         */
        if (
          parseInt(this.experimentTime) > 0 &&
          timeInterval >= parseInt(this.experimentTime)
        ) {
          // this.stop();
          this.timestamp =
            this.startstamp + parseInt(this.experimentTime) * 1000 * 60;
          var arr = this.calculateDiffTime(this.startstamp, this.timestamp);
          let timeInterval = arr[0] * 60 + arr[1];
          this.usetime =
            this.zeroPadding(arr[0], 2) +
            ":" +
            this.zeroPadding(arr[1], 2) +
            ":" +
            this.zeroPadding(arr[2], 2);
          // 超时签退
          this.stop();
          // console.log(this.calculateDiffTime(this.startstamp,timestamp));
        }
      }
    },
    /* 监测预习测试模块的测试已用时长是否超时 */
    checkExtime() {
      /* 若 预习测试已交卷 或者 还未点击开始测试 不做超时监测 */
      if (this.isFinishtest == 1 || this.isStartTest == 0) {
        return;
      }
      /**
       * 已开始测试后(未交卷)，计时 已用时长(秒) 和 测试剩余时间
       */
      this.qstuUseTimeSeconds++; // 记录从开始测试到当前的秒数，避免取客户端时间
      var timeInterval = Math.floor(this.qstuUseTimeSeconds / 60); // 时间间隔(分钟)
      var arr = this.calculateDiffTime(
        this.extime * 60 * 1000,
        this.qstuUseTimeSeconds * 1000
      ); // 毫秒值差值
      this.qstuRemainTime =
        this.zeroPadding(arr[0], 2) +
        ":" +
        this.zeroPadding(arr[1], 2) +
        ":" +
        this.zeroPadding(arr[2], 2);
      /**
       * 判断 当前预习题测试时长(分钟) timeInterval 是否 超过(大于等于) 预习题规定时长(分钟) extime
       * 		若超过了预习题规定时长，则自动结束预习答题，并自动执行【提交】操作
       *     【即要保证学生在规定的有限时长内完成预习，超时自动将预习题交卷】
       *
       * 增加 parseInt(this.extime) > 0 的判断，避免定时器执行到此处时，后端请求数据还未到达，导致默认值 0
       */
      // this.extime = 1; // 1分钟超时测试
      if (
        parseInt(this.extime) > 0 &&
        timeInterval >= parseInt(this.extime) &&
        this.isFinishtest == 0
      ) {
        for (var i = 0; i < this.stuselect.length; i++) {
          if (this.stuselect[i].select.length == 0) {
            if (this.stuselect[i].correctSelect.indexOf(",") == -1) {
              this.stuselect[i].select = -1;
            } else {
              this.stuselect[i].select.push(-1);
            }
          }
        }
        // 超时自动交卷
        this.complete();
      }
    },
    /* 开始测试按钮 */
    startTestBtn() {
      let param = {
        stuid: this.user.id,
        exid: this.$route.params.id,
      };
      this.assignQues = 1; // 分配题目
      this.getTestQuesetion();
      // getStuQuesUseTime(param).then(res =>{
      // 	if(res.data.code == 200){
      // 		this.$message({
      // 			message: res.data.msg,
      // 			type: 'success'
      // 		});
      // 		this.isStartTest = 1; // 开始测试标志
      // 		this.qstuUseTimeSeconds = res.data.data; // 取当前已用时间(秒)
      // 	}else{
      // 		this.$message.error(res.data.msg);
      // 	}
      // 	// console.log(res.data);
      // }).catch(ret => {
      // 	this.$message.error("网络连接失败！请检查");
      // })
    },
    handleCheckedCitiesChange(value) {
      console.log(value);
      console.log(this.stuselect);
    },
    zeroPadding(num, digit) {
      var zero = "";
      for (var i = 0; i < digit; i++) {
        zero += "0";
      }
      return (zero + num).slice(-digit);
    },
    //签到
    start() {
      if (this.starttime == "") {
        // console.log(this.startstamp);
        let param = {
          stuid: this.user.id,
          exid: this.$route.params.id,
          startstamp: this.startstamp,
        };
        this.testLoading = true;
        SigninStart(param)
          .then((res) => {
            this.testLoading = false;
            if (res.data.code == 200) {
              this.$message({
                message: res.data.msg,
                type: "success",
              });
              this.starttime = this.time;
              this.startstamp = this.timestamp;
            } else {
              this.$message.error(res.data.msg);
            }
            // console.log(res.data);
          })
          .catch((ret) => {
            this.testLoading = false;
            this.$message.error("网络连接失败！请检查");
          });
      } else {
        this.$message({
          message: "警告哦，您已经签到过了！",
          type: "warning",
        });
      }
    },
    //签退
    stop() {
      if (this.starttime != "") {
        if (this.endtime == "") {
          let param = {
            stuid: this.user.id, //学生id
            exid: this.$route.params.id, //实验ID
            endstamp: this.timestamp, //结束时间戳
          };
          SigninOut(param)
            .then((res) => {
              // console.log(res.data.data);
              if (res.data.code == 200) {
                this.$message({
                  message: res.data.msg,
                  type: "success",
                });
                let cd = new Date(this.timestamp);
                this.endtime =
                  this.zeroPadding(cd.getFullYear(), 4) +
                  "-" +
                  this.zeroPadding(cd.getMonth() + 1, 2) +
                  "-" +
                  this.zeroPadding(cd.getDate(), 2) +
                  " " +
                  this.zeroPadding(cd.getHours(), 2) +
                  ":" +
                  this.zeroPadding(cd.getMinutes(), 2) +
                  ":" +
                  this.zeroPadding(cd.getSeconds(), 2);
                this.endstamp = this.timestamp;
                this.stopflag = true;
              } else {
                this.$message.error(res.data.msg);
                this.stopflag = true;
              }
            })
            .catch((ret) => {
              this.$message.error("网络连接失败！请检查");
              this.stopflag = true;
            });
        } else {
          this.$message({
            message: "警告！已经签退过了，如果是误点，请联系老师",
            type: "warning",
          });
        }
      } else {
        this.$message({
          message: "警告哦，您需要先进行签到！",
          type: "warning",
        });
      }
    },
    // 计算实验开始&结束时间差，并以 [小时:分钟:秒] 的数组格式返回
    calculateDiffTime(start_time, end_time) {
      var startTime = 0,
        endTime = 0;
      if (start_time < end_time) {
        startTime = start_time;
        endTime = end_time;
      } else {
        startTime = end_time;
        endTime = start_time;
      }
      var timeDiff = (endTime - startTime) / 1000;
      var hour = Math.floor(timeDiff / 3600);
      timeDiff = timeDiff % 3600;
      var minute = Math.floor(timeDiff / 60);
      timeDiff = timeDiff % 60;
      var second = timeDiff;
      return [hour, minute, second];
    },
    //获取测试题
    getTestQuesetion() {
      let param = {
        stuid: this.user.id,
        exid: this.$route.params.id,
        assignQues: this.assignQues,
      };
      this.testLoading = true;
      getStuQuestion(param).then((res) => {
        // console.log(res.data.data);
        this.testLoading = false;
        if (res.data.code == 200) {
          this.experimentTime = res.data.data.experimentinfo.etime; // 实验时长(分)
          this.extime = res.data.data.experimentinfo.extime; // 预习测试时长(分)
          this.singleCount = res.data.data.singleCount; // 单个单选题分值权重
          this.multiCount = res.data.data.multiCount; // 单个多选题分值权重
          this.questions = res.data.data.questions; // 实验的题目列表和对应选项列表信息
          var qstu = Object.assign([], res.data.data.qstu); // 学生实验课程关联表(表示学生是否分配到实验题目)
          for (var i = 0; i < this.questions.length; i++) {
            // 遍历实验题目
            this.stuselect.push({
              exid: this.questions[i].exid,
              id: this.questions[i].id,
              select: [],
              correctSelect: this.questions[i].correctAnswer,
            });
            this.questions[i].correctArr = this.questions[i].correctAnswer
              .split(",")
              .map(Number)
              .sort();
            for (var j = 0; j < this.questions[i].soptions.length; j++) {
              // 遍历实验题目的选项
              this.questions[i].soptions[j].isture = 0;
              for (var k = 0; k < this.questions[i].correctArr.length; k++) {
                if (
                  Number(this.questions[i].soptions[j].flag) ==
                  this.questions[i].correctArr[k]
                ) {
                  this.questions[i].soptions[j].isture = 1;
                }
              }
            }
          }
          // console.log(res.data.data);
          // 若已经分配过题目，则加载学生的回答信息
          var stuselect = [];
          var qtrue = [];
          if (qstu.length > 0) {
            // 如果已经分配过实验题目，则判断是否交卷
            this.isFinishtest = Number(qstu[0].isfinishtest);
            this.isStartTest = 1; // 开始测试标志
            this.qstuUseTimeSeconds = qstu[0].usetime; // 预习测试已用时长(秒)
            if (this.isFinishtest == 1) {
              // 若已经交卷
              stuselect = qstu[0].answer.split(";"); // 取出学生回答答案的列表
              qtrue = qstu[0].qtrue.split(",").map(Number); // 取出学生回答答案是否正确的列表
            }
            // 注：回答答案列表中，每题答案用 ";" 分隔，每个多选题内的多个答案用 "," 分隔
            for (var i = 0; i < stuselect.length; i++) {
              stuselect[i] = stuselect[i].split(",").map(Number);
            }
            for (var i = 0; i < this.questions.length; i++) {
              // 遍历该实验的所有题目
              this.questions[i].isture = qtrue[i]; // 标记该题目回答是否正确
              this.questions[i].stuselect = stuselect[i]; // 取出该题学生给出的答案
            }
          }
        } else {
          this.$message.error(res.data.msg);
        }
      });
    },
    // 获取实验报告标题及内容基本信息(目的及原理)
    getExperimentS() {
      let param = {
        id: this.$route.params.id,
      };
      getExperimentContent(param).then((res) => {
        // console.log(res.data.data);
        this.purpose = res.data.data;
      });
    },
    // 查询实验报告 部分 客观题（单选/多选/填空题）
    getExperimentsObjectiveQuestion() {
      let param = {
        exid: this.$route.params.id,
        stuid: this.user.id,
      };
      queryObjectiveQuestionResult(param).then((res) => {
        if (res.data.code == 200) {
          this.ObjectiveQuestions = res.data.data;
          for (let i = 0; i < this.ObjectiveQuestions.length; i++) {
            let questionResult = this.ObjectiveQuestions[i].questionResult;
            if (this.ObjectiveQuestions[i].type != 3) {
              this.AnswerResult.push({
                stuid: this.user.id,
                objectiveQuestionId: this.ObjectiveQuestions[i].id,
                answerValue: !!questionResult ? questionResult.answerValue : [],
              });
            } else {
              this.AnswerResult.push({
                stuid: this.user.id,
                objectiveQuestionId: this.ObjectiveQuestions[i].id,
                answerValue: !!questionResult ? questionResult.answerValue : "",
              });
            }
          }
        } else {
          this.$message.error(res.data.msg);
        }
      });
    },
    //主观题
    getExperimentsConclusion() {
      let param = {
        stuid: this.user.id,
        exid: this.$route.params.id,
      };
      getExperimentResult(param).then((res) => {
        this.ponder = res.data.data;
        let param = {
          stuid: this.user.id,
          exid: this.$route.params.id,
        };
        getStuExreport(param)
          .then((res1) => {
            // console.log(res1.data.data);
            // console.log(this.ponder);
            let content = Object.assign([], res1.data.data);
            // console.log(content);
            for (var i = 0; i < this.ponder.length; i++) {
              for (var j = 0; j < content.length; j++) {
                if (this.ponder[i].id == content[j].titleid) {
                  this.content[i] = content[j].content;
                }
              }
            }
            // console.log(this.content);
          })
          .catch((ret) => {
            this.$message.error("网络连接失败！请检查");
          });
      });
    },
    //预测题交卷
    complete() {
      let falg = false;
      let score = 0;
      let Allscore = 0;
      // 如果有题目没有选择答案，则不准提交
      for (var i = 0; i < this.stuselect.length; i++) {
        if (this.stuselect[i].select.length == 0) {
          falg = true;
        }
      }
      if (this.isFinishtest == 1) {
        return false;
      }
      if (falg) {
        this.$message.error("请先完成题目！");
        return false;
      } else {
        // console.log(this.stuselect);
        // return false;

        /* 得分计算 */
        for (var i = 0; i < this.stuselect.length; i++) {
          var solution = this.stuselect[i].correctSelect.split(",");
          // console.log(this.stuselect[i].select.length);
          if (typeof this.stuselect[i].select == "number") {
            if (Number(solution[0]) == Number(this.stuselect[i].select)) {
              score += Number(this.singleCount);
              this.questions[i].isture = 1;
            } else {
              this.questions[i].isture = 0;
            }
            var arr = [];
            arr.push(this.stuselect[i].select);
            this.questions[i].stuselect = arr;
            Allscore += Number(this.singleCount);
          } else {
            var stuanswer = this.stuselect[i].select.sort().toString();
            var correctanswer = solution.sort().toString();
            if (stuanswer == correctanswer) {
              score += Number(this.multiCount);
              this.questions[i].isture = 1;
            } else {
              this.questions[i].isture = 0;
            }
            this.questions[i].stuselect = this.stuselect[i].select.sort();
            Allscore += Number(this.multiCount);
          }
        }
        // 计算得分后，标记已交卷
        this.isFinishtest = 1;
        this.stuscore = score;
        this.Allscore = Allscore;
        this.isPassScore = Number(Allscore * 0.6); // 判断得分是否通过
        if (this.isPassScore < this.stuscore) {
          this.isPass = 1;
        } else {
          this.isPass = 0;
        }
        // this.centerDialogVisible = true;
        // console.log(Allscore);
        // console.log(this.questions);
        let isture = []; //是否正确
        let stuselect = []; //学生回答问题
        for (var j = 0; j < this.questions.length; j++) {
          isture[j] = this.questions[j].isture;
          stuselect[j] = this.questions[j].stuselect.join(",");
        }
        let param = {
          stuid: this.user.id,
          exid: this.$route.params.id,
          qtrue: isture.join(","),
          answer: stuselect.join(";"),
          score: score,
          allscore: Allscore,
          isfinishtest: 1,
          flag: this.isPass,
        };
        submitFinish(param)
          .then((res) => {
            if (res.data.code == 200) {
              this.centerDialogVisible = true;
            } else {
              this.$message.error(res.data.msg);
            }
          })
          .catch((ret) => {
            this.$message.error("网络连接失败！请检查");
          });
      }
    },
    // 保存客观题
    saveObjective() {
      let param = {
        isHandIn: 0, // 是否交卷 0 否；1 是
        AnswerResult: this.AnswerResult,
      };
      saveObjectiveQuestionResult(param).then((res) => {
        if (res.data.code == 200) {
          this.$message({
            message: res.data.msg,
            type: "success",
          });
        } else {
          this.$message.error(res.data.msg);
        }
      });
    },
    //主观题保存(交卷)
    saveSubjective(index) {
      if (
        typeof this.content[index] == "undefined" ||
        this.content[index] == ""
      ) {
        this.$message.error("请输入答案内容");
      } else {
        let param = Object.assign({}, this.ponder[index]);
        param.content = this.content[index];
        param.stuid = this.user.id;
        this.testLoading = true;
        submitExreport(param)
          .then((res) => {
            this.testLoading = false;
            if (res.data.code == 200) {
              this.$message({
                message: res.data.msg,
                type: "success",
              });
            } else {
              this.$message.error(res.data.msg);
            }
          })
          .catch((ret) => {
            this.testLoading = false;
            this.$message.error("上传图片可能过大，请注意");
          });
      }
    },
    //saveSummary实验总结
    saveSummary() {
      if (typeof this.summary == "undefined" || this.summary == "") {
        this.$message.error("请输入答案内容");
      } else {
        let param = {
          exid: this.$route.params.id,
        };
        param.content = this.summary;
        param.stuid = this.user.id;
        // console.log(param);
      }
    },
    /* 查询该学生在该实验上的签到信息 */
    SearchSign() {
      let param = {
        stuid: this.user.id,
        exid: this.$route.params.id,
      };
      Signin(param)
        .then((res) => {
          // console.log(res.data.data);
          if (res.data.data.length > 0 && res.data.data[0].stime != null) {
            this.starttime = res.data.data[0].stime1; // 签到开始时间(时分秒格式)
            this.startstamp = res.data.data[0].stime * 1000; // 签到开始时间(时间戳格式)
          }
          if (res.data.data.length > 0 && res.data.data[0].etime != null) {
            this.endtime = res.data.data[0].etime1; // 签到结束时间(时分秒格式)
            this.endstamp = res.data.data[0].etime * 1000; // 签到结束时间(时间戳格式)
            // 计时器置停
            this.stopflag = true;
            // 计算实验开始结束的时长，返回[时,分,秒]数组，并以字符串拼接，如：01:02:32
            var arr = this.calculateDiffTime(this.startstamp, this.endstamp);
            this.usetime =
              this.zeroPadding(arr[0], 2) +
              ":" +
              this.zeroPadding(arr[1], 2) +
              ":" +
              this.zeroPadding(arr[2], 2);
          }
        })
        .catch((ret) => {
          this.$message.error("网络连接失败！请检查");
        });
    },
    // 提交所有表单（客观题/主观题）
    submitAllForm() {
      if (this.AnswerResult.length <= 0) {
        this.$message({
          message: "提交报告成功！",
          type: "success",
        });
        return;
      }
      // 客观题表单校验
      let valid = true;
      for (let i = 0; i < this.AnswerResult.length; i++) {
        if (this.AnswerResult[i].answerValue.length == 0) {
          valid = false;
          break;
        }
      }
      if (valid) {
        // 只需处理客观题的提交（自动批卷），主观题已保存（不做处理）
        let param = {
          isHandIn: 1, // 是否交卷 0 否；1 是
          AnswerResult: this.AnswerResult,
        };
        saveObjectiveQuestionResult(param).then((res) => {
          if (res.data.code == 200) {
            this.$message({
              message: "提交报告成功！",
              type: "success",
            });
            // 查询实验报告 部分 客观题（单选/多选/填空题）
            this.getExperimentsObjectiveQuestion();
          } else {
            this.$message.error(res.data.msg);
          }
        });
      } else {
        this.$message.error("请先完成客观题！");
        return false;
      }
    },
  },
  mounted() {
    /* 1. 预习测试 和 实验签到 的定时超时监测 部分 */
    this.timer = setInterval(this.updateTime, 1000);
    this.updateTime();
    /* 从客户端会话中获取用户信息 */
    var user = sessionStorage.getItem("user");
    if (user) {
      this.user = JSON.parse(user);
    }
    // /* 2. 预习测试 部分 */
    this.getTestQuesetion();
    /* 3. 目的及原理 部分 */
    this.getExperimentS();
    /* 4. 填写实验报告 部分 客观题（单选/多选/填空题） */
    this.getExperimentsObjectiveQuestion();
    /* 5. 填写实验报告 部分 主观题（思考题） */
    this.getExperimentsConclusion();
    /* 查询签到信息 */
    this.SearchSign();
  },
};
</script>
<style scoped>
.excontent {
  width: 97%;
  margin: 0 auto;
  margin-top: 50px;
  margin-bottom: 50px;
}
.test-select {
  width: 95%;
  margin: 0 auto;
}
.test-header {
  height: 50px;
  line-height: 50px;
  font-size: 16px;
  color: #333;
}
.test-start {
  margin-left: 0%;
  /* margin-top: 50px; */
}
.submit {
  margin-left: 45%;
  margin-top: 50px;
}
.sign p {
  margin: 20px;
}
.second_content {
  height: 500px;
  background: #0f3854;
  background: -webkit-radial-gradient(center ellipse, #0a2e38 0%, #000000 70%);
  background: radial-gradient(ellipse at center, #0a2e38 0%, #000000 70%);
  background-size: 100%;
}
.sign {
  font-family: "Share Tech Mono", monospace;
  color: #ffffff;
  text-align: center;
  position: absolute;
  left: 50%;
  top: 50%;
  -webkit-transform: translate(-50%, -50%);
  transform: translate(-50%, -50%);
  color: #daf6ff;
  text-shadow: 0 0 20px #0aafe6, 0 0 20px rgba(10, 175, 230, 0);
}
.time {
  letter-spacing: 0.05em;
  font-size: 50px;
  padding: 5px 0;
}
.date {
  letter-spacing: 0.1em;
  font-size: 24px;
}
.list {
  height: 20px;
  line-height: 20px;
  background-color: #409eff;
  display: inline-block;
  padding: 10px 15px;
  border-radius: 25px;
  color: #fff;
}
.list_content {
  width: 95%;
  margin: 0 auto;
}
.btn_save_center {
  height: 100%;
  text-align: center;
  margin-top: 10px;
}
.btnsave {
  height: 100%;
  text-align: right;
  margin-top: 10px;
}
.tagtitle {
  width: 150px;
  line-height: 50px;
  font-size: 20px;
  color: red;
  margin: 0 auto;
  padding-top: 200px;
}
.pselectdiv {
  width: 95%;
  margin: 0 auto;
}
.pselect {
  height: 40px;
  line-height: 40px;
  border: 1px solid #dcdfe6;
  border-radius: 4px;
  box-sizing: border-box;
  color: #606266;
  font-weight: 500;
  font-size: 14px;
  width: 70%;
  padding-left: 20px;
}
.pselectdiv span {
  font-size: 14px;
  margin-bottom: 20px;
  color: #666;
}
.flagRed {
  color: #ff6547 !important;
}
.flagBlue {
  color: #25bb9b !important;
}
.BorderBlue {
  border-color: #25bb9b !important;
}
.BorderRed {
  border-color: #ff6547 !important;
}
.dialogp {
  text-align: center;
  font-size: 15px;
}
.second_title {
  font-size: 16px;
  font-weight: bold;
  padding: 10px 0px;
}
</style>
<style type="text/css">
.el-radio.is-bordered {
  width: 100% !important;
}
.el-radio + .el-radio {
  margin-left: 0px !important;
  margin-top: 20px !important;
}
.el-radio.is-bordered + .el-radio.is-bordered {
  margin-left: 0px !important;
}
.ql-container {
  height: 300px !important;
}
.el-radio-group {
  width: 70% !important;
}
.el-checkbox.is-bordered {
  width: 70% !important;
}
.el-checkbox.is-bordered + .el-checkbox.is-bordered {
  margin-left: 0px !important;
  margin-top: 20px !important;
}
.questionImageClass {
  margin-left: 3%;
  font-weight: bold;
}
/*.el-radio.is-bordered.is-checked{
		border-color: red;
	}
	.el-radio__input.is-checked+.el-radio__label{
		color: red;
	}
	.el-radio__input.is-checked .el-radio__inner{
		border-color: red;
  		background: red;
	}*/
</style>
