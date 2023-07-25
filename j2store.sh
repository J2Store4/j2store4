echo "J2Store Pro pack"
current_dir="$PWD"
echo "Current Dir $current_dir"
form_folder="$current_dir/j2store4"
echo "ZIP folder $form_folder"
compress_folder="com_j2store_pro"
copy_folder(){
  move_dir=("administrator" "components" "fof" "language" "media" "modules" "plugins" "com_j2store.xml" "LICENSE" "README.md" "script.j2store.php")
  pack_compress_folder="$current_dir/$compress_folder"
  if [ -d "$pack_compress_folder" ]
  then
     rm -r "$compress_folder"
     mkdir "$compress_folder"
     for dir in ${move_dir[@]}
     do
       cp -r "$form_folder/$dir" "$pack_compress_folder/$dir"
     done
  else
    mkdir "$compress_folder"
    for dir in ${move_dir[@]}
    do
      cp -r "$form_folder/$dir" "$pack_compress_folder/$dir"
    done
  fi
}
zip_folder(){
  move_dir=( "components" "fof" "language" "media" "modules" "plugins" "com_j2store.xml" "LICENSE" "README.md" "script.j2store.php")
   rm "$compress_folder".zip
  if [ -d "$pack_compress_folder" ]
    then
      cd $compress_folder
       zip_folder_base="administrator"
       zip -r "com_j2store_v4-4.0.XX-pro".zip $zip_folder_base
       for dir in ${move_dir[@]}
       do
         zip_folder_next="$dir"
         zip -ur "com_j2store_v4-4.0.XX-pro".zip $zip_folder_next
       done
  fi
}
copy_folder
zip_folder