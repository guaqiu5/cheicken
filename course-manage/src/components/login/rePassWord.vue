<template>
	<el-container style="min-width: 1100px;height: 100vh;">
	  	<el-header style="height: 70px">
	  		<el-row :gutter="20">
			  <el-col :span="6" :offset="2">
			  		<div class="head_png">
				  	  <img src="@/assets/logo-red.svg">
				  	 	<span  class="head_title">密码重置</span>
			  	 	</div>
			  </el-col>
			  <el-col :span="8" :offset="8">
				 <ul class="head_meun">
				 	<li><router-link to="home">首页</router-link><span>|</span></li>
				 	<li><a href="">在线教育</a><span>|</span></li>
				 	<li><router-link to="home">帮助文档</router-link></li>
				 </ul>
			  </el-col>
			</el-row>
	  	</el-header>
	  	<el-main>
	  		<div class="main_content">
	  			<div class="main_content_left"><img src="@/assets/tg-mj1e9c5d.jpg"></div>
	  			<div class="main_content_right" v-loading="LoginLodding">
	  				<p>密码重置入口</p>
	  				<el-form ref="form" :model="login" label-width="120px" class="login_form" :rules="rules">
	  					<el-form-item label="账号" prop="num">
					    	<el-input v-model="login.num"  placeholder="请输入学号" disabled></el-input>
						</el-form-item>
	  				<el-form-item label="密码" prop="pwd">
					    <el-input v-model="login.pwd" placeholder="请输入密码" type="password"></el-input>
            </el-form-item>
            <el-form-item label="输入重置密码" prop="repwd1">
					    <el-input v-model="login.repwd1" placeholder="请输入密码" type="password"></el-input>
            </el-form-item>
            <el-form-item label="再次输入密码" prop="repwd2">
					    <el-input v-model="login.repwd2" placeholder="请输入密码" type="password"></el-input>
						</el-form-item>
						<el-form-item>
						    <el-button type="success" plain @click="submitForm('form')">立即重置</el-button>
						    <el-button type="warning" plain @click="resetForm('rules')">取消</el-button>
						</el-form-item>
					</el-form>
	  			</div>
         <!--  <div>
              <p style="margin: 100px auto;width: 190px;color: #67C23A;font-size: 25px;">
                <i class="el-icon-success"></i>
                密码重置成功
              </p>
          </div> -->
         <!--  <div class="jumbotron">
            <i class="el-icon-warning"></i>
            <p class="jumbotronp">温馨提示：</p>
            <p class="jumbotronp">请先登录以获取你的考试信息</p>
            <el-button type="primary" class="jumbotronpbtn">登录</el-button>
          </div> -->
	  		</div>
	  	</el-main>
	    <exfooter></exfooter>
	</el-container>
</template>

<script type="text/javascript">
  import exfooter from '../footer/footer.vue'
  import { requestRePassword } from '@/api/api';
	export default {
		name:"login",
    components:{
      exfooter
    },
		data(){
			return {
				login:{
					num:'',//学号
          pwd:'',//密码
          repwd1:'',//重置密码
          repwd2:'',//重置密码二
				},
				rules:{
					num:[{required: true, message: '请输入账户', trigger: 'blur' }],
          pwd:[{required: true, message: '请输入密码', trigger: 'blur' }],
          repwd1:[{required: true, message: '请输入密码', trigger: 'blur' }],
          repwd2:[{required: true, message: '请输入密码', trigger: 'blur' }],
				},//规则定义
        LoginLodding:false
			}
		},
    methods:{
      submitForm(formName){
        this.$refs[formName].validate((valid) =>{
          if(valid){
            if(this.login.repwd1 !== this.login.repwd2){
              this.$message.error("两次密码输入不一致");
              return;
            }
            this.LoginLodding=true;
            requestRePassword(this.login).then(ret => {
                this.LoginLodding=false;
                if(ret.data.code === 1){
                  this.$message({
                    message: ret.data.msg,
                    type: 'success'
                  });
                  sessionStorage.setItem('user', '');
                  this.$router.push({
                    path:'/login'
                  })
                }else{
                  this.$message.error(ret.data.msg);
                }
             }).catch(ret =>{
               this.LoginLodding=false;
               this.$message.error("请检查网络！");
             })
          }else{
            return false;
          }
        })
      },
      resetForm(){
        this.login.num = "";
        this.login.pwd = "";
      }
    },
    mounted(){
      let user = JSON.parse(sessionStorage.getItem('user'));
      if(user){
        this.login.num = user.num;
      }else{
        this.$router.push('/login'); 
      }
    }
	}
</script>
<style scoped>
	.el-header, .el-footer {
    color: #333;
    height: 70px;
  }
  .el-header{
  	border-bottom: 1px solid #e7e7e7;
  }
  .el-main {
    color: #333;
  }
  .header_btn{
  	display: inline-block;
  }
  .head_title{
  	color: #333;
  	font-size: 26px;
  	line-height: 70px;
  	vertical-align:middle;
  }
  img{
  	vertical-align:middle;
  }
  .head_png{
  	cursor:pointer;
  }
  .head_meun{
  	list-style: none;
  	margin-top: 0px;
  	line-height: 70px;
  }
  .head_meun li{
  	float: left;
  }
  .head_meun >li >a{
  	text-decoration: none;
  	padding-left: 20px;
  	padding-right: 20px;
  	color: #777;
  	font-size: 15px;
  }
  .head_meun >li >a:hover{
  	color: #333;
  }
  .main_content{
  	padding: 50px 0px;
  	height: 400px;
  	width:1000px;
  	margin:0px auto;
  }
  .main_content_left{
  	float: left;
  }
  .main_content img{
  	width:380px;
  	height: 231.73px;
  	padding-top: 60px;
  }
  .main_content_right{
  	border: 1px solid #ccc;
  	height: 400px;
  	width:500px;
  	float: right;
  	border-radius: 8px;
  	box-shadow: 0px 0px 5px #888888;
  }
  .main_content_right p{
    margin-top: 5px;
    margin-bottom: 10px;
    color: #737373;
    text-align: center;
    font-size: 15px;
    line-height: 60px;
    width:300px;
    margin:0 auto;
    border-bottom: 1px solid #e7e7e7;
  }
  .login_form{
  	width:400px;
  	padding: 40px 20px;
  }
  .jumbotron{
    height: 400px;
    margin: 0 auto;
    background-color: #eee;
    border-radius: 6px;
  }
  .el-icon-warning{
    font-size: 60px;
    width: 100%;
    text-align: center;
    margin-top: 100px;
  }
  .jumbotronp{
    margin-bottom: 15px;
    font-size: 21px;
    font-weight: 200;
    text-align: center;
  }
  .jumbotronpbtn{
    margin-top: 10px;
    margin-left: 40%;
    width: 200px;
    height: 50px;
  }
</style>