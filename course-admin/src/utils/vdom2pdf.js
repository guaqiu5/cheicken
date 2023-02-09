import html2canvas from "html2canvas";
import jsPDF from "jspdf";
 const vdom2pdf = (_this,vdomRef,name='默认') => {
    var w = parseInt(window.getComputedStyle(vdomRef).width);
    // 图片宽度
    var h = parseInt(window.getComputedStyle(vdomRef).height);
    //滚轮置顶，避免留白
    window.pageYOffset = 0;
    document.documentElement.scrollTop = 0;
    document.body.scrollTop = 0;
    // 利用 html2canvas 下载 canvas
    html2canvas(vdomRef).then(function(canvas) {
      //转化为本地的图片地址
      _this.imgURL = canvas.toDataURL();
      var filename = `${name}.pdf`;
      //一页pdf显示html页面生成的canvas高度;
      var pageHeight = (w / 592.28) * 841.89;
      //未生成pdf的html页面高度
      var leftHeight = h;
      //页面偏移
      var position = 0;
      //a4纸的尺寸[595.28,841.89]，html页面生成的canvas在pdf中图片的宽高
      var imgWidth = 595.28;
      var imgHeight = (592.28 / w) * h;
     // new jsPDF("l", "pt", "a4"); l 表示横向导出；true表示压缩pdf，否则文件会很大
      var pdf = new jsPDF("p", "pt", "a4");
      //有两个高度需要区分，一个是html页面的实际高度，和生成pdf的页面高度(841.89)
      //当内容未超过pdf一页显示的范围，无需分页
      if (leftHeight < pageHeight) {
        pdf.addImage(_this.imgURL, "JPEG", 0, 0, imgWidth, imgHeight);
      } else {
        // 分页
        while (leftHeight > 0) {
          pdf.addImage(
            _this.imgURL,
            "JPEG",
            0,
            position,
            imgWidth,
            imgHeight
          );
          leftHeight -= pageHeight;
          position -= 841.89;
          //避免添加空白页
          if (leftHeight > 0) {
            pdf.addPage();
          }
        }
      }
      //转pdf下载
      pdf.save(filename);
 })
}

export default vdom2pdf
