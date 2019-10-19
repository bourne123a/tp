echo "loading..."
pid=`pidof tp_master`
echo $pid
kill -USR1 $pid
echo "loading success"