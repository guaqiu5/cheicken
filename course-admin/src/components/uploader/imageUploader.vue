<template>
    <div class="vue-uploader">
        <div class="file-list">
            <section v-for="(file, index) of files" class="file-item draggable-item">
                <img :src="file.imageBase64Content" alt="" ondragstart="return false;">
                <p class="file-name">{{file.name}}</p>
                <span class="file-remove" @click="remove(index)">+</span>
            </section>
            <section class="file-item">
                <div @click="add" class="add">
                    <span>+</span>
                </div>
            </section>
        </div>
        <section v-if="status=='ready'" class="upload-func">
			      <div class="progress-bar">
                <span style="color:red">上传/删除图片后，均需点击右侧 “保存图片” 按钮生效</span>
            </div>
            <div class="operation-box">
                <el-button @click.prevent="save">保存图片</el-button>
            </div>
        </section>
        <input type="file" accept="image/*" @change="fileChanged" ref="file" multiple="multiple">
    </div>
</template>
<script>
// 参考链接：https://www.cnblogs.com/goloving/p/9236421.html
export default {
  props: {
    relatedObject: {
      type: Object,
      required: true,
    },
    loadFunc: {
      type: Function,
      required: true,
    },
    saveFunc: {
      type: Function,
      required: true,
    },
  },
  data() {
    return {
      files: [],
      status: 'finished',
    };
  },
  methods: {
    load() {
      this.loadFunc(this.relatedObject, this.setFilesFunc);
      this.finishFunc();
    },
    setFilesFunc(files) {
      this.files = files;
    },
    save() {
      this.saveFunc(this.files, this.relatedObject, this.finishFunc);
    },
    finishFunc() {
      this.status = 'finished';
    },
    readyFunc() {
      this.status = 'ready';
    },
    add() {
      this.$refs.file.click(); //调用file的click事件
    },
    remove(index) {
      this.files.splice(index, 1);
      this.readyFunc();
    },
    fileChanged() {
      const list = this.$refs.file.files;
      for (let i = 0; i < list.length; i++) {
        if (!this.isContain(list[i])) {
          const item = {
            name: list[i].name,
            size: list[i].size,
          };
          this.html5Reader(list[i], item);
          this.files.push(item);
          this.readyFunc();
        }
      }
      this.$refs.file.value = "";
    },
    // 将图片文件转成BASE64格式
    html5Reader(file, item) {
      const reader = new FileReader();
      reader.onload = (e) => {
        this.$set(item, "imageBase64Content", e.target.result);
      };
      reader.readAsDataURL(file);
    },
    isContain(file) {
      return this.files.find(
        (item) => item.name === file.name && item.size === file.size
      );
    },
  },
  created() {
    this.load();
  },
  watch: {
    relatedObject: {
      handler(newV, oldV) {
        this.load();
      },
      // deep: true,
    },
  },
};
</script>
<style>
.vue-uploader {
  border: 1px solid #e5e5e5;
}
.vue-uploader .file-list {
  padding: 10px 0px;
}
.vue-uploader .file-list:after {
  content: "";
  display: block;
  clear: both;
  visibility: hidden;
  line-height: 0;
  height: 0;
  font-size: 0;
}
.vue-uploader .file-list .file-item {
  float: left;
  position: relative;
  width: 100px;
  text-align: center;
}
.vue-uploader .file-list .file-item img {
  width: 80px;
  height: 80px;
  border: 1px solid #ececec;
}
.vue-uploader .file-list .file-item .file-remove {
  position: absolute;
  right: 12px;
  display: none;
  top: 4px;
  width: 14px;
  height: 14px;
  color: white;
  cursor: pointer;
  line-height: 12px;
  border-radius: 100%;
  transform: rotate(45deg);
  background: rgba(0, 0, 0, 0.5);
}
.vue-uploader .file-list .file-item:hover .file-remove {
  display: inline;
}
.vue-uploader .file-list .file-item .file-name {
  margin: 0;
  height: 40px;
  word-break: break-all;
  font-size: 14px;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}
.vue-uploader .add {
  width: 80px;
  height: 80px;
  margin-left: 10px;
  float: left;
  text-align: center;
  line-height: 80px;
  border: 1px dashed #ececec;
  font-size: 30px;
  cursor: pointer;
}
.vue-uploader .upload-func {
  display: flex;
  padding: 10px;
  margin: 0px;
  background: #f8f8f8;
  border-top: 1px solid #ececec;
}
.vue-uploader .upload-func .progress-bar {
  flex-grow: 1;
}
.vue-uploader .upload-func .progress-bar section {
  margin-top: 5px;
  background: #00b4aa;
  border-radius: 3px;
  text-align: center;
  color: #fff;
  font-size: 12px;
  transition: all 0.5s ease;
}
.vue-uploader .upload-func .operation-box {
  flex-grow: 0;
  padding-left: 10px;
}
.vue-uploader .upload-func .operation-box button {
  padding: 10px 8px;
  color: #fff;
  background: #007acc;
  border: none;
  border-radius: 2px;
  cursor: pointer;
}
.vue-uploader > input[type="file"] {
  display: none;
}
</style>