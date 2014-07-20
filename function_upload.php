	<?php  

	public function store(){
		
		$file = Input::file('image');
		
		//Upload folder
		$destino = 'public/images-games';
		//$this->upload_file($file,$folder, image_size, thumb_size);
		$callback = $this->upload_file($file,$destino, 400, 110);
		
		if($callback['error'] != 1){ 
			echo '<h2>'.$callback['filename'].'<h2>';
		}else{
			echo $callback['msg'];
			#var_dump($callback);
		}

	}

	public function upload_file($file, $destino, $resize=0, $thumb=0){
		
		$mime = $file->getMimeType();
		

		$nombre_final = str_replace(' ', '-', $file->getClientOriginalName());
		$callback = array('error' => '', 'filename' => '', 'msg' => '' );
		$mime = $file->getMimeType();

		if($file->getSize() <= 0 ){
			$callback['error'] = 1;
			$callback['msg'] .= "File size 0";
		}elseif($file->getSize() > $peso_maximo = 2000000000){
			$callback['error'] = 1;
			$callback['msg'] .= "File size exceed";
		}elseif(file_exists($destino.'/'.$nombre_final)){
			//existe agrego un random
			srand((double)microtime()*1000000);
            $nombre_final = rand(0,20000)."_".$nombre_final;
			
		}

		if( $callback['error'] != 1 ){
			//upload
			$file->move($destino, $nombre_final);
			$callback['filename']=$nombre_final;

		}

		function validate_image($extencion_mime){
			//check extencion
			$file_extensions_allowed = array('image/gif', 'image/png', 'image/jpeg', 'image/jpg');
		
			if(!in_array($extencion_mime, $file_extensions_allowed)){
				return false;
				exit;
			}
			return true;
		}
		

		//ORIGINAL IMAGE
		

		if($resize > 0 && $callback['error'] != 1){
			if(validate_image($mime)){
				//es imagen, puedo cambiar tamaño
				$this->resize($destino, $nombre_final, 800);
			}
		}

		

		//THUMBNAIL
		if($thumb > 0 && $callback['error'] != 1){
			var_dump($callback);
			if(validate_image($mime)){
				
				//es imagen, puedo cambiar tamaño
				if (!copy($destino.'/'.$nombre_final, $destino.'/tn_'.$nombre_final)){
					$callback['error'] = 1;
       				$callback['msg'] .= "failed to copy image for thumbnail.";
      			}
      			$nombre_thumb = 'tn_'.$nombre_final;
      			//call function thumbnail
      			$this->resize($destino, $nombre_thumb, 120);
      		}
		}
		return $callback;
	}//end upload_file



		/* FUNCTION RESIZE */
		function resize($destino, $nombre_file, $widthsize){
			ini_set('memory_limit','128M');
			$nombre_final = $nombre_file;
			$max_width = $widthsize;
			$max_height = 0;
			
			if(preg_match("/\.(jpg|jpeg|JPG)/",$nombre_final)){
				$base = ImageCreateFromJPEG ($destino.'/'.$nombre_final);
			}
			if(preg_match("/\.(png)/",$nombre_final)){
				$base = ImageCreateFromPNG ($destino.'/'.$nombre_final);
				
			}
			if(preg_match("/\.(gif)/",$nombre_final)){

				$base = ImageCreateFromGif ($destino.'/'.$nombre_final);

			}
			
			$FullImage_width = imagesx ($base);    
        	$FullImage_height = imagesy ($base); 


        	if(isset($max_width) && isset($max_height) && $max_width != 0 && $max_height != 0){
         		$new_width = $max_width;
         		$new_height = $max_height;
        	}
        	else if(isset($max_width) && $max_width != 0){
         		$new_width = $max_width;
         		$new_height = ((int)($new_width * $FullImage_height) / $FullImage_width);
        	}
        	else if(isset($max_height) && $max_height != 0){
         		$new_height = $max_height;
         		$new_width = ((int)($new_height * $FullImage_width) / $FullImage_height);
        	}else{
         		$new_height = $FullImage_height;
         		$new_width = $FullImage_width;
        	}
        	//create
        	$resized_image =  ImageCreateTrueColor ( $new_width , $new_height );

			
			
        	if(preg_match("/\.png/",$nombre_final)){
        		//for tranparency in pNG
        		imagealphablending($resized_image, FALSE);
        		imagesavealpha($resized_image, TRUE);
        	}

        	ImageCopyResampled ( $resized_image, 
        		$base, 0,0,0,0, 
        		$new_width, 
        		$new_height, 
        		$FullImage_width, 
        		$FullImage_height 
        		);

        	if(preg_match("/\.png/",$nombre_final)){
      



				ImagePNG( $resized_image, $destino.'/'.$nombre_final,0);
        	}

        	if(preg_match("/\.gif/",$nombre_final)){
        		//background for gif without black background
        		imagecolorallocate($resized_image, 255, 255, 255);
				
				ImageGIF($resized_image, $destino.'/'.$nombre_final);
			}
			if(preg_match("/\.(jpg|jpeg|JPG)/",$nombre_final)){ImageJPEG( $resized_image, $destino.'/'.$nombre_final,99);}
        	
        	
        	ImageDestroy( $resized_image );
        	unset($max_width);
        	unset($max_height);
        	unset($base); unset($nombre_final);
		}

		/* END FUNCTION RESIZE */	

?>