import React , {Component} from 'react';
import axios from 'axios';
import {Link, withRouter} from 'react-router-dom';
import dataCode from '../../dataCode';
import { ClipLoader } from 'react-spinners';
import * as actions from '../../store/actions/index';
import Alert from 'react-s-alert';
import Modal from 'react-responsive-modal';
import { connect } from 'react-redux';
import './showSearchProductResult.css';
import URLs from "../../URLs";
import styles from './custom-styling.css';
import FilterProducts from './FilterProducts/FilterProducts';
import ProductTable from './ProductsTable/ProductsTable';
import MultiCategory from './MultiCategory/MultiCategory';
import QueryString from 'query-string';

let prices = {};let counter = 0;

class showSearchProductResult extends Component {

    state  = {
        searchKey: '', data: '', dataParts: [], dataCode: '', dataFilters: [],open: false, prices: {}, projects: [],
        tableHeaderS: '', loading: true, number: 1,loadingAddCart: true,productName: '', category: '',
        projectName: null, multiCategory: [],
    }

    componentDidMount() {prices = {};counter = 0;
        let url = URLs.base_URL+URLs.search_part_category+this.props.match.params.category+'&keyword='+this.props.match.params.keyword;
        let newURL = {
            category: this.props.match.params.category, keyword: this.props.match.params.keyword,
        }
        if(this.props.match.params.filter !== undefined) {
            console.log('showSearchProductResult componentDidMount filter not null');
            console.log(this.props.match.params.filter);
            url = url+"&filters=&"+this.props.match.params.filter
            // url = url+this.props.match.params.filter;
            // newURL["filter"] = QueryString.parse(this.props.match.params.filter)
        }
        // let temp = window.location.href;
        // temp = temp.replace(URLs.react_search_url+this.props.match.params.category+'/'+this.props.match.params.keyword,'');
        // temp = temp.replace('/','');
        // console.log('componentDidMount temp');console.log(temp);
        console.log('componentDidMount newURL');
        console.log(url);
        // let test = QueryString.stringify({
        //     category: this.props.match.params.category,
        //     keyword: this.props.match.params.keyword,
        //     nested: JSON.stringify({
        //         filter: this.props.match.params.filter
        //     })
        // }, {arrayFormat: 'bracket'});
        // console.log(test);
        // // console.log(QueryString.stringify(newURL))
        // console.log(QueryString.parse(test));
        // let temp = JSON.parse( QueryString.parse(test).nested );
        // console.log(temp)
        // console.log(QueryString.parse( temp.filter));
        // // if(temp !== '') { url = url + '&filters='+temp; }
        let keyword = this.props.match.params.keyword;
        // if(keyword.includes("&subcategory=")) {keyword = keyword.split("&subcategory=")[0];}
        this.setState({searchKey: keyword});
        // url = "http://localhost/api/search-part-filter?keyword=stm32f4&category=Embedded-Microcontrollers&filters=%7B%22rCl%22:[%2240MHz%22],%22tra%22:[%22Microchip+Technology%22]%7D";
        axios.get(url)
            .then(response => {
                console.log("componentDidMount showSearchProductResult");
                console.log(response);
                // console.log(dataCode.partSearch);
                if(response.data[0] === dataCode.partSearch) {
                    // console.log("IS EQUAL");
                    this.setState({dataCode: response.data[0],dataParts: response.data[2],dataFilters: response.data[3],tableHeaderS: response.data[5],
                        category: response.data[6] });
                } else if(response.data[0] === dataCode.partSearchMultiCategory) {
                    this.setState({dataCode: response.data[0], multiCategory: response.data[1]});
                    console.log("componentDidMount showSearchProductResult multiCategory");
                } else { console.log("componentDidMount showSearchProductResult none of them"); }
                this.setState({loading: false});
            })
            .catch(err => {
                console.log("componentDidMount searchKey");console.log(err);
            });
        if(this.props.token) { this.getProjects(); }
    }

    sort = (property,kind) => {
        console.log("sort");
        console.log(property);
        console.log(kind);
    }

    filterComponent = (filters) => {
        console.log("filterComponent filters");
        console.log(filters);
        console.log("filterComponent new packages");
        // let urlParams = Object.keys(filters).map(function(k) {
        //     return encodeURIComponent(k) + '=' + encodeURIComponent(urlParams[k])
        // }).join('&')
        const stringified = QueryString.stringify(filters, {arrayFormat: 'bracket'});

        console.log(stringified);
        console.log("filterComponent new packages parse");
        console.log(QueryString.parse(stringified));
        let url = '/search/'+this.state.category.name+'/'+this.props.match.params.keyword+'/'+stringified;
        // url = url.replace('{',"%7B");
        // url = url.replace('}',"%7D");
        // let url = buildUrl('/search/'+this.state.dataParts[0].slug+'/'+this.props.match.params.keyword+'/', {
        //     queryParams: {
        //         'filters': JSON.stringify(filters)
        //     }
        // });
        console.log("filterComponent url");
        console.log(url);
        // url = url.replace('?filters=','/');
        // console.log(url);
        this.props.history.push(url);
        window.location.reload();
    }

    // setNumber = (e,num) => {
    //     // console.log('num');console.log(num);console.log('e');console.log(e.target.value);
    //    let temp = this.state.number;temp[num] = e.target.value; this.setState({number: temp});
    // }

    addToCart = (productName,category,number) => {
       if(this.props.token) {
           this.setState({loadingAddCart: true});
           console.log("number of products :");console.log(number);
           axios.post(URLs.base_URL+URLs.user_cart_create, {
               keyword: productName,
               num: number,
               token: this.props.token, project: this.state.projectName
           })
               .then(response => {
                   console.log("add to cart function");
                   console.log(response);console.log("this.state.projectName");console.log(this.state.projectName);
                   this.props.addToCart(productName, number, category, this.state.projectName);
                   Alert.success(response.data, {
                       position: 'bottom-right',
                       effect: 'scale',
                       beep: false,
                       timeout: 4000,
                       offset: 100
                   });
                   this.setState({loadingAddCart: false});
               })
               .catch(err => {
                   console.log("showSearchProductResult add to cart  error");
                   console.log(err);
                   Alert.error('دوباره امتحان کنید', {
                       position: 'bottom-right',
                       effect: 'scale',
                       beep: false,
                       timeout: 4000,
                       offset: 100
                   });
                   this.setState({loadingAddCart: false});
               });

       } else {
           this.props.addToCart(productName, number, category, null);
           Alert.success('به سبد خرید اضافه شد', {
               position: 'bottom-right',
               effect: 'scale',
               beep: false,
               timeout: 4000,
               offset: 100
           });
       }
       this.onCloseModal();
    }

    setInitialForPriceInput = () => {
        let temp = this.state.number;
        if(Object.keys(temp).length == 0) {
            this.state.dataParts.map((item, i) => {
                 Object.keys(item).map((property, j) => {
                    if(property === "unit_price") {
                        temp[item['manufacturer_part_number']] = 1 ;
                        prices[item['manufacturer_part_number']] = 0;
                    }
                });
            });
            console.log("setInitialForPriceInput");console.log(temp);
            this.setState({number: temp});let pricesBuf = {};
            Object.keys(prices).map((property, j) => {
                console.log(j+" : "+ property);
                axios.post(URLs.base_URL+URLs.product_get_price, {keyword: property})
                    .then(response => {
                        console.log(j+" : ");counter++;
                        console.log(response);
                        pricesBuf[property] = response.data.unit_price;
                        if(counter === Object.keys(prices).length) {
                            console.log("get last response ");console.log(pricesBuf);
                            this.setState({prices: pricesBuf, loadingAddCart: false});
                        }
                    })
                    .catch(err => {
                        console.log(err);
                    });

            });

         }
    }

    onOpenModal = (productName,category,number) => {
        console.log("showSearchProductResult open Modal");
        console.log("category");console.log(category);
        console.log("productName");console.log(productName);
        console.log("number");console.log(number);
        if(this.props.token) {
            this.setState({open: true});
            this.setState({productName: productName, category: category, number: number});
        } else {
            this.addToCart(productName,category,number);
        }
    };

    onCloseModal = () => {
        this.setState({ open: false });
    };

    getProjects = () => {
        axios.post(URLs.base_URL+URLs.user_get_projects, {token: this.props.token})
            .then(response => {
                console.log("projects");console.log(response);
                this.setState({projects: response.data});
            })
            .catch(err => {
                console.log("get projects error");
                console.log(err);
            });
    }

    selectChange = (event) => {
        // console.log("showSearchProductResult select changes");
        // console.log(event.target.value);
        let temp = event.target.value;
        if(temp === '-'){temp = null;}
        this.setState({projectName: temp});
    }

    render() {
        let productsTble;
        let projectsOption;let filterProduct;let multiCAtegory;
        if(this.state.dataCode === dataCode.partSearch) {
            productsTble = <ProductTable onOpenModal={this.onOpenModal} sort={this.sort} searchKey={this.state.searchKey} tableHeaderS={this.state.tableHeaderS} dataParts={this.state.dataParts} />;
            if(this.state.projects.length > 0) {
                projectsOption = this.state.projects.map((project, i) => {
                    return (<option value={project.name} key={project.name}>{project.name}</option>)
                });
            }
            filterProduct = <FilterProducts filterComponent={this.filterComponent} tableHeaderS={this.state.tableHeaderS} dataFilters={this.state.dataFilters} loading={this.state.loading} />
        } else if(this.state.dataCode === dataCode.partSearchMultiCategory) {
            multiCAtegory = <MultiCategory category={this.state.multiCategory} cat={this.props.match.params.category} keyword={this.props.match.params.keyword} />
        }
        return(
            <div className="container table-responsive text-center searchResultContainer">
               <div>
                <ClipLoader loaderStyle={{size: '200'}} color={'#123abc'} loading={this.state.loading} />
               </div>
                {multiCAtegory}
                {filterProduct}
                {productsTble}
                <Modal open={this.state.open} onClose={this.onCloseModal} center
                       classNames={{overlay: styles.customOverlay, modal: styles.customModal,}}>
                  <div className="select-project">
                    <h3 className="text-center"> انتخاب پروژه</h3>
                    <br/>
                    <div className="col-lg-4 col-md-6 col-sm-10 horizontal-center">
                        <select className="form-control" value={this.state.projectName}  onChange={this.selectChange}>
                            <option value={null}>-</option>
                            {projectsOption}
                        </select>
                    </div>
                    <br/>
                    <button onClick={()=> this.addToCart(this.state.productName, this.state.category, this.state.number)} className="btn btn-success horizontal-center">اضافه به سبد خرید</button>
                    <br/>
                  </div>
                </Modal>
            </div>
        )
    }
}

const mapDispatchToProps = dispatch => {
    return {
        addToCart: (productName,number,category,projectName) => dispatch(actions.addToCart(productName,number,category,projectName)),
    };
};

const mapStateToProps = state => {
    return {
        token: state.auth.token,
    };
};

export default connect(mapStateToProps,mapDispatchToProps)(withRouter(showSearchProductResult));