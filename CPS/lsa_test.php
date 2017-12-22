<?php
	$dscArray_array[0]='我想我們應該要想辦法了解冷劑';
	$dscArray_array[1]='我們來分工合作找資料吧!';
	$dscArray_array[2]='不如我上網查查看關於冷劑的資料吧！';
	$dscArray_array[3]='其實我也不大了解，我們一起上網查資料吧！';  	
	$dscArray_array[4]='我也不太了解我們先來了解一下冷劑'; 
	$dscArray_array[5]='我也不知道那我們去請教師長和同學'; 
	$dscArray_array[6]='我們有需要了解嗎? ';
	$dscArray_array[7]='我們應該不需要使用冷劑吧';        	
  for($x=0;$x<count($dscArray_array);$x++){
		$term1=$speech_data;
		$term2=$dscArray_array[$x];
		document_document_txt($term1,$term2);
		$str=file_get_contents("./output/document1.txt");
		$txt_vector_1=document_vector($str,"300","lsa_term","lsa_u","lsa_s","lsa_v"); //計算文件的語意向量
		$str2=file_get_contents("./output/document2.txt");
		$txt_vector_2=document_vector($str2,"300","lsa_term","lsa_u","lsa_s","lsa_v"); //計算文件的語意向量
		$txt_vector[1]=$txt_vector_1;
		$txt_vector[2]=$txt_vector_2;
		$getValue = document_sim($txt_vector,"lsa_s");
		if($getValue<0){
			$getValue=0;
		}else{
			if($getValue>$max_value){
			$max_value = $getValue;
			$return_value = $dscIndex_array[$x];
			}
		}

	}
	echo $return_value;
}

// 文件斷詞          
function document_document_txt($term1,$term2){
         $document1=$term1;
         $document1= mb_convert_encoding($document1, "big5","utf-8"); 
         $fp=fopen("./input/document1.txt","w");
         fputs($fp,$document1);
         fclose($fp);
         $document2=$term2;
         $document2= mb_convert_encoding($document2, "big5","utf-8"); 
         $fp=fopen("./input/document2.txt","w");
         fputs($fp,$document2);
         fclose($fp);
         exec("java -jar CkipClient.jar ckipsocket.propeties input output");
} 
?>
    

