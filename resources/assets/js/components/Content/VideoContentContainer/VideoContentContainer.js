import React , {Component} from 'react';
import { Link } from 'react-router-dom';
import URLs from '../../../URLs';
import VideoContent from '../VideoContent/VideoContent';
import axios from 'axios';
import { ClipLoader } from 'react-spinners';

class VideoContentContainer extends Component {
    state = {
      videos: [], counter: 1, loading: true
    }

    componentDidMount() {
        // axios.get(URLs.base_URL+URLs.get_videos)
        //     .then((res) => {
        //         console.log('res get videos');
        //         console.log(res);
        //         this.setState({videos: res.data});
        //     })
        //     .catch((error)=> {
        //         console.log('error get videos');
        //         console.log(error);
        //     });
        this.moreVideos();
    }

    moreVideos = (e) => {
        console.log('get more videos');console.log("num="+this.state.counter);
        axios.get(URLs.base_URL+URLs.get_more_videos+"?num="+this.state.counter)
            .then((res) => {
                console.log('res get more videos');
                console.log(res);
                let videos = [...this.state.videos, ...res.data];
                this.setState({videos: videos, loading: false});
            })
            .catch((error)=> {
                this.setState({loading: false});
                console.log('error get more videos');
                console.log(error);
            });
        this.setState({counter: this.state.counter+1});
    }

    render() {
        const videos = this.state.videos.map((obj,i) => {
            // if(i<4) {
                return <VideoContent key={i} url={obj.frame} title={obj.title}/>
            // }
        });
        return (
            <div>
                <h2 className="text-center" style={{marginTop: "1%", marginBottom: '1%'}}>ویدیوها</h2>
                <div className="flex space-around flex-wrap">
                    <ClipLoader size="200" color={'#123abc'} loading={this.state.loading} />
                    {videos}
                </div>
                <button onClick={this.moreVideos} className="btn btn-primary col-md-2 col-sm-4" style={{margin: "auto", display: "block", width: "80px"}}>بیشتر</button>
                <br/>
            </div>
        )
    }
}

export default VideoContentContainer;